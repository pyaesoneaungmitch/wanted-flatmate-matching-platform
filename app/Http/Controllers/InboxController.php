<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InboxController extends Controller
{
    // List threads + optionally open first thread
    public function index()
    {
        $me = Auth::user()->user_id;

        // Backfill: create missing inbox rows for existing matches
        $this->ensureMatchThreadsExist($me);

        $tab = request()->query('tab', 'people');

        $threads = $this->getThreadsForUser($me);
        $threadsFiltered = $threads->filter(fn($t) => $tab === 'people' ? $t->type === 'MATCH' : $t->type === 'LISTING');

        if ($threadsFiltered->count() > 0) {
            return redirect()->to(
            route('inbox.show', ['inbox_id' => $threadsFiltered->first()->inbox_id]) . '?tab=' . $tab
        );
        }

        return view('inbox.index', [
            'threads' => $threads,
            'activeThread' => null,
            'messages' => collect(),
            'activeUser' => null,
        ]);
    }

    public function show(int $inbox_id)
    {
        $me = Auth::user()->user_id;

        $this->ensureMatchThreadsExist($me);

        $threads = $this->getThreadsForUser($me);

        $activeThread = DB::table('inbox')
            ->where('inbox_id', $inbox_id)
            ->whereIn('type', ['MATCH','LISTING'])
            ->where(function($q) use ($me){
                $q->where('user1_id',$me)->orWhere('user2_id',$me);
            })
            ->first();
            

        abort_if(!$activeThread, 404);
        $activeThread->other_user_id = ($activeThread->user1_id == $me) ? $activeThread->user2_id : $activeThread->user1_id;
        $otherId = ($activeThread->user1_id == $me) ? $activeThread->user2_id : $activeThread->user1_id;
        $match = DB::table('matches')->where('match_id', $activeThread->match_id)->first();
        $activeThread->other_user_id = ($activeThread->user1_id == $me) ? $activeThread->user2_id : $activeThread->user1_id;

        $listingTitle = null;
        $listingDeleted = false;

        if ($activeThread->type === 'LISTING') {
            $listingRow = DB::table('listings')
                ->where('listing_id', $activeThread->listing_id)
                ->first(['property_name','deleted_at']);

            $listingTitle = $listingRow->property_name ?? 'Listing Enquiry';
            $listingDeleted = ($listingRow && $listingRow->deleted_at !== null);
        }


        $iShared = false;
        if ($activeThread->type === 'MATCH' && $activeThread->match_id) {
            $match = DB::table('matches')->where('match_id', $activeThread->match_id)->first();
            if ($match) {
                $iShared = ($me === (int)$match->user1_id)
                    ? ((int)$match->user1_shared === 1)
                    : ((int)$match->user2_shared === 1);
            }
        }
        $activeUser = DB::table('public_profile')
            ->where('user_id', $otherId)
            ->first();

        $messages = DB::table('messages')
            ->where('inbox_id', $inbox_id)
            ->orderBy('sent_at')
            ->get();

        return view('inbox.index', [
            'threads' => $threads,
            'activeThread' => $activeThread,
            'messages' => $messages,
            'activeUser' => $activeUser,
            'iShared' => $iShared,
            'listingTitle' => $listingTitle,
            'listingDeleted' => $listingDeleted,
        ]);
    }

    public function send(Request $request, int $inbox_id)
    {
        $me = Auth::user()->user_id;

        $data = $request->validate([
            'content' => ['required','string','max:1000'],
        ]);

        $thread = DB::table('inbox')
            ->where('inbox_id', $inbox_id)
            ->whereIn('type', ['MATCH','LISTING'])
            ->where(function($q) use ($me){
                $q->where('user1_id',$me)->orWhere('user2_id',$me);
            })
            ->first();

        abort_if(!$thread, 404);

        
        if ($thread->type === 'LISTING') {
        $deletedAt = DB::table('listings')
            ->where('listing_id', $thread->listing_id)
            ->value('deleted_at');

        if ($deletedAt !== null) {
            return redirect()->to(route('inbox.show', ['inbox_id' => $inbox_id]) . '?tab=listings')
                ->with('listing_deleted', true);
        }
    }
        DB::table('messages')->insert([
            'inbox_id' => $inbox_id,
            'sender_user_id' => $me,
            'content' => $data['content'],
            'sent_at' => now(),
        ]);

        // optional: track latest activity (if you add last_message_at later)
        $tab = request()->query('tab', 'people');
        return redirect()->to(route('inbox.show', ['inbox_id' => $inbox_id]) . '?tab=' . $tab);
    }

    public function sharePrivate(int $inbox_id)
    {
        $me = Auth::user()->user_id;

        $thread = DB::table('inbox')
            ->where('inbox_id', $inbox_id)
            ->where('type', 'MATCH')
            ->where(function($q) use ($me){
                $q->where('user1_id',$me)->orWhere('user2_id',$me);
            })
            ->first();

        

            $match = DB::table('matches')->where('match_id', $thread->match_id)->first();
            abort_if(!$match, 404);

            $me = Auth::user()->user_id;

            $u1 = (int)$match->user1_id;
            $u2 = (int)$match->user2_id;

            $justShared = false;

            if ($me === $u1) {
                $new = ((int)$match->user1_shared === 1) ? 0 : 1;
                DB::table('matches')->where('match_id', $match->match_id)->update(['user1_shared' => $new]);
                $justShared = ($new === 1);
            } elseif ($me === $u2) {
                $new = ((int)$match->user2_shared === 1) ? 0 : 1;
                DB::table('matches')->where('match_id', $match->match_id)->update(['user2_shared' => $new]);
                $justShared = ($new === 1);
            } else {
                abort(403);
            }

            // Optional system message
            DB::table('messages')->insert([
                'inbox_id' => $inbox_id,
                'sender_user_id' => $me,
                'content' => $justShared
                    ? '[System] Private profile shared.'
                    : '[System] Private profile sharing turned off.',
                'sent_at' => now(),
            ]);

            return redirect()
                ->route('inbox.show', ['inbox_id' => $inbox_id])
                ->with('share_status', $justShared ? 'shared' : 'stopped');
    }
    public function unmatch(int $inbox_id)
    {
        $me = Auth::user()->user_id;

        $thread = DB::table('inbox')
            ->where('inbox_id', $inbox_id)
            ->where('type', 'MATCH')
            ->where(function($q) use ($me){
                $q->where('user1_id',$me)->orWhere('user2_id',$me);
            })
            ->first();

        abort_if(!$thread, 404);

        // Delete match (cascades could remove inbox/messages if FK is ON DELETE CASCADE)
        DB::table('matches')->where('match_id', $thread->match_id)->delete();

        // Ensure thread/messages are removed even if FK cascade isn't set
        DB::table('messages')->where('inbox_id', $inbox_id)->delete();
        DB::table('inbox')->where('inbox_id', $inbox_id)->delete();

        return redirect()->route('inbox');
    }

    private function getThreadsForUser(int $me)
        {
            return DB::table('inbox as i')
                ->leftJoin('listings as l', 'l.listing_id', '=', 'i.listing_id')
                ->where(function($q) use ($me){
                    $q->where('i.user1_id', $me)->orWhere('i.user2_id', $me);
                })
                ->where(function($q) use ($me){
                    $q->where('i.type', 'MATCH')
                    // show LISTING only if I'm NOT the owner of that listing
                    ->orWhere(function($qq) use ($me){
                        $qq->where('i.type', 'LISTING')
                            ->whereRaw('(l.user_id IS NULL OR l.user_id <> ?)', [$me]);
                    });
                })
                ->orderByDesc('i.created_at')
                ->get([
                    'i.inbox_id','i.type','i.user1_id','i.user2_id','i.match_id','i.listing_id','i.created_at',
                    DB::raw('l.user_id as listing_owner_id'),
                    DB::raw('l.property_name as listing_title'),
                ])
                ->map(function($t) use ($me) {
                    $t->other_user_id = ((int)$t->user1_id === (int)$me) ? (int)$t->user2_id : (int)$t->user1_id;
                    return $t;
                });
        }

    private function ensureMatchThreadsExist(int $me): void
    {
        // For all my matches, ensure inbox row exists
        $matches = DB::table('matches')
            ->where('user1_id',$me)->orWhere('user2_id',$me)
            ->get(['match_id','user1_id','user2_id']);

        foreach ($matches as $m) {
            DB::table('inbox')->updateOrInsert(
                [
                    'type' => 'MATCH',
                    'user1_id' => min($m->user1_id, $m->user2_id),
                    'user2_id' => max($m->user1_id, $m->user2_id),
                    'match_id' => $m->match_id,
                ],
                [
                    'listing_id' => null,
                    'created_at' => now(),
                ]
            );
        }
    }
}
