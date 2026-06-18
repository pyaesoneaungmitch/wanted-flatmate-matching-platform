<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyListingsController extends Controller
{
    public function index()
    {
        $me = Auth::user()->user_id;

        $rows = DB::table('listings as l')
            ->leftJoin(DB::raw('(SELECT listing_id, MIN(photo_url) AS photo_url FROM property_photos GROUP BY listing_id) ph'),
                'ph.listing_id', '=', 'l.listing_id'
            )
            ->where('l.user_id', $me)
            ->whereNull('l.deleted_at')
            ->orderByDesc('l.updated_at')
            ->get([
                'l.listing_id','l.property_name','l.updated_at',
                'ph.photo_url'
            ]);

        // enquiry count = number of LISTING threads per listing
        $counts = DB::table('inbox')
            ->where('type', 'LISTING')
            ->whereIn('listing_id', $rows->pluck('listing_id')->all())
            ->select('listing_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('listing_id')
            ->get()
            ->keyBy('listing_id');

        $cards = $rows->map(function($r) use ($counts){
            return [
                'listing_id' => (int)$r->listing_id,
                'property_name' => $r->property_name,
                'updated_at' => $r->updated_at,
                'photo_url' => $r->photo_url,
                'enquiry_count' => (int)($counts[$r->listing_id]->cnt ?? 0),
            ];
        });

        return view('my_listings.index', ['cards' => $cards]);
    }

    public function edit(int $listing_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')
            ->where('listing_id', $listing_id)
            ->where('user_id', $me)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$listing, 404);

        $propertyPhotos = DB::table('property_photos')
            ->where('listing_id', $listing_id)
            ->orderBy('photo_id')
            ->get();

        return view('my_listings.edit', [
            'listing' => $listing,
            'propertyPhotos' => $propertyPhotos,
        ]);
    }

    public function update(Request $request, int $listing_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')
            ->where('listing_id', $listing_id)
            ->where('user_id', $me)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$listing, 404);

        $data = $request->validate([
            'property_name' => ['required','string','min:3','max:80'],
            'max_occupants' => ['required','integer','min:1','max:255'],
            'rent_pcm' => ['required','integer','min:0','max:10000'],
            'city' => ['required','string','max:60'],
            'property_type' => ['required','in:ROOM,STUDIO,FLAT,HOUSE'],
            'bathrooms_shared' => ['required','boolean'],
            'available_from' => ['nullable','date'],
            'description' => ['nullable','string','max:800'],
            'contact_email' => ['nullable','email','max:255'],
            'contact_phone' => ['nullable','string','max:30'],
        ]);

        if (empty($data['contact_email']) && empty($data['contact_phone'])) {
            return back()->withErrors(['contact_email' => 'Provide at least one contact method (email or phone).']);
        }

        DB::table('listings')
            ->where('listing_id', $listing_id)
            ->update([
                'property_name' => $data['property_name'],
                'max_occupants' => $data['max_occupants'],
                'rent_pcm' => $data['rent_pcm'],
                'city' => $data['city'],
                'property_type' => $data['property_type'],
                'bathrooms_shared' => $data['bathrooms_shared'] ? 1 : 0,
                'available_from' => $data['available_from'] ?? null,
                'description' => $data['description'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('my.listings')->with('saved', true);
    }

    public function destroy(int $listing_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')
            ->where('listing_id', $listing_id)
            ->where('user_id', $me)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$listing, 404);

        DB::table('listings')
            ->where('listing_id', $listing_id)
            ->update(['deleted_at' => now()]);

        return redirect()->route('my.listings')->with('deleted', true);
    }

    // Owner-side enquiries inbox (per listing)
    public function enquiries(int $listing_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')
            ->where('listing_id', $listing_id)
            ->where('user_id', $me)
            ->first();

        abort_if(!$listing, 404);

        $threads = DB::table('inbox as i')
            ->where('i.type', 'LISTING')
            ->where('i.listing_id', $listing_id)
            ->orderByDesc('i.created_at')
            ->get(['i.inbox_id','i.user1_id','i.user2_id','i.created_at'])
            ->map(function($t) use ($me){
                $t->other_user_id = ((int)$t->user1_id === (int)$me) ? (int)$t->user2_id : (int)$t->user1_id;
                return $t;
            });

        if ($threads->count() > 0) {
            return redirect()->route('my.listings.enquiries.show', ['listing_id'=>$listing_id, 'inbox_id'=>$threads->first()->inbox_id]);
        }

        return view('my_listings.enquiries', [
            'listing' => $listing,
            'threads' => $threads,
            'activeThread' => null,
            'messages' => collect(),
            'activeUser' => null,
        ]);
    }

    public function enquiriesShow(int $listing_id, int $inbox_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')
            ->where('listing_id', $listing_id)
            ->where('user_id', $me)
            ->first();

        abort_if(!$listing, 404);

        $threads = DB::table('inbox as i')
            ->where('i.type', 'LISTING')
            ->where('i.listing_id', $listing_id)
            ->orderByDesc('i.created_at')
            ->get(['i.inbox_id','i.user1_id','i.user2_id','i.created_at'])
            ->map(function($t) use ($me){
                $t->other_user_id = ((int)$t->user1_id === (int)$me) ? (int)$t->user2_id : (int)$t->user1_id;
                return $t;
            });

        $activeThread = DB::table('inbox')
            ->where('inbox_id', $inbox_id)
            ->where('type', 'LISTING')
            ->where('listing_id', $listing_id)
            ->first();

        abort_if(!$activeThread, 404);

        $otherId = ((int)$activeThread->user1_id === (int)$me) ? (int)$activeThread->user2_id : (int)$activeThread->user1_id;

        $activeUser = DB::table('public_profile')->where('user_id', $otherId)->first();

        $messages = DB::table('messages')
            ->where('inbox_id', $inbox_id)
            ->orderBy('sent_at')
            ->get();

        return view('my_listings.enquiries', [
            'listing' => $listing,
            'threads' => $threads,
            'activeThread' => $activeThread,
            'messages' => $messages,
            'activeUser' => $activeUser,
        ]);
    }

    public function enquiriesSend(Request $request, int $listing_id, int $inbox_id)
    {
        $me = Auth::user()->user_id;

        $data = $request->validate([
            'content' => ['required','string','max:1000'],
        ]);

        // ensure owner + correct listing
        $listing = DB::table('listings')
            ->where('listing_id', $listing_id)
            ->where('user_id', $me)
            ->first();
        abort_if(!$listing, 404);

        $thread = DB::table('inbox')
            ->where('inbox_id', $inbox_id)
            ->where('type', 'LISTING')
            ->where('listing_id', $listing_id)
            ->first();
        abort_if(!$thread, 404);

        DB::table('messages')->insert([
            'inbox_id' => $inbox_id,
            'sender_user_id' => $me,
            'content' => $data['content'],
            'sent_at' => now(),
        ]);

        return redirect()->route('my.listings.enquiries.show', ['listing_id'=>$listing_id, 'inbox_id'=>$inbox_id]);
    }
}
