<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListingsController extends Controller
{
    public function index(Request $request)
    {
        $me = Auth::user()->user_id;

        $sort = $request->query('sort', 'price'); // price|city|updated
        $city = $request->query('city');
        $min = $request->query('min');
        $max = $request->query('max');

        $q = DB::table('listings as l')
            ->whereNull('l.deleted_at')
            ->join('public_profile as p', 'p.user_id', '=', 'l.user_id')
            ->leftJoin(DB::raw('(SELECT listing_id, MIN(photo_url) AS photo_url FROM property_photos GROUP BY listing_id) ph'),
                'ph.listing_id', '=', 'l.listing_id'
            )
            ->select([
                'l.*',
                'p.display_name as owner_name',
                'ph.photo_url as photo_url'
            ]);
        $q->orderByRaw('CASE WHEN l.user_id = ? THEN 0 ELSE 1 END', [$me]);

        if ($city) $q->where('l.city', $city);
        if ($min !== null && $min !== '') $q->where('l.rent_pcm', '>=', (int)$min);
        if ($max !== null && $max !== '') $q->where('l.rent_pcm', '<=', (int)$max);

        if ($sort === 'city') $q->orderBy('l.city');
        elseif ($sort === 'updated') $q->orderByDesc('l.updated_at');
        else $q->orderBy('l.rent_pcm'); // price

        $listings = $q->limit(50)->get();
        $listingIds = $listings->pluck('listing_id')->all();

        $photosByListing = DB::table('property_photos')
            ->whereIn('listing_id', $listingIds)
            ->orderBy('photo_id')
            ->get(['listing_id','photo_url'])
            ->groupBy('listing_id');
            
        $cities = DB::table('listings')
        ->whereNull('deleted_at')
        ->select('city')->distinct()->orderBy('city')->pluck('city');

        return view('listings.index', [
            'listings' => $listings,
            'cities' => $cities,
            'sort' => $sort,
            'city' => $city,
            'min' => $min,
            'max' => $max,
            'photosByListing' => $photosByListing,
        ]);
    }

    public function create(Request $request)
        {
            $me = Auth::user()->user_id;
            $listingId = $request->query('listing_id');

            $listing = null;
            $propertyPhotos = collect();

            if ($listingId) {
                $listing = DB::table('listings')
                    ->where('listing_id', (int)$listingId)
                    ->where('user_id', $me)
                    ->first();

                if ($listing) {
                    $propertyPhotos = DB::table('property_photos')
                        ->where('listing_id', (int)$listingId)
                        ->orderBy('photo_id')
                        ->get();
                }
            }

            return view('listings.create', [
                'listing' => $listing,
                'propertyPhotos' => $propertyPhotos,
                'listingId' => $listingId,
            ]);
        }

    public function store(Request $request)
    {
        $me = Auth::user()->user_id;

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

        // Require at least one contact method
        if (empty($data['contact_email']) && empty($data['contact_phone'])) {
            return back()->withErrors(['contact_email' => 'Provide at least one contact method (email or phone).']);
        }

        $listingId = DB::table('listings')->insertGetId([
            'user_id' => $me,
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

        return redirect()->route('listings.create', ['listing_id' => $listingId]);
    }

    public function uploadPhoto(Request $request, int $listing_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')->where('listing_id', $listing_id)->first();
        abort_if(!$listing || (int)$listing->user_id !== (int)$me, 403);

        $data = $request->validate([
            'photo' => ['required','image','mimes:jpg,jpeg,png,webp','max:5120'],
        ]);

        $path = $data['photo']->store('property_photos', 'public');

        DB::table('property_photos')->insert([
            'listing_id' => $listing_id,
            'photo_url' => '/storage/'.$path,
            'uploaded_at' => now(),
        ]);

        return back();
    }

    public function enquire(int $listing_id)
    {
        $me = Auth::user()->user_id;

        $listing = DB::table('listings')->where('listing_id', $listing_id)->first();
        if ($listing->deleted_at !== null) {
        return redirect()->route('listings')->with('listing_deleted_notice', true);
        }
        if ($listing->deleted_at !== null) {
        return redirect()->route('listings')->with('listing_deleted_notice', true);
        }

        abort_if(!$listing, 404);
        abort_if($listing->deleted_at !== null, 410);

        $owner = (int)$listing->user_id;
        if ($owner === $me) {
            return redirect()->to(route('inbox.show', ['inbox_id' => $inboxId]) . '?tab=listings');
        }

        $u1 = min($me, $owner);
        $u2 = max($me, $owner);

        // Create LISTING inbox thread (unique per listing + pair)
        $threadId = DB::table('inbox')->updateOrInsert(
            [
                'type' => 'LISTING',
                'user1_id' => $u1,
                'user2_id' => $u2,
                'listing_id' => $listing_id,
            ],
            [
                'match_id' => null,
                'created_at' => now(),
            ]
        );

        // Grab inbox_id (updateOrInsert doesn’t return id; find it)
        $inboxId = DB::table('inbox')
            ->where('type','LISTING')
            ->where('user1_id',$u1)->where('user2_id',$u2)
            ->where('listing_id',$listing_id)
            ->value('inbox_id');

        // Optional: insert a starter system message
        DB::table('messages')->insert([
            'inbox_id' => $inboxId,
            'sender_user_id' => $me,
            'content' => "[System] Enquiry about: {$listing->property_name}",
            'sent_at' => now(),
        ]);

        return redirect()->route('inbox.show', ['inbox_id' => $inboxId]);
    }

    public function deletePhoto(int $photo_id)
        {
            $me = Auth::user()->user_id;

            $row = DB::table('property_photos')->where('photo_id', $photo_id)->first();
            abort_if(!$row, 404);

            $listing = DB::table('listings')->where('listing_id', $row->listing_id)->first();
            abort_if(!$listing || (int)$listing->user_id !== (int)$me, 403);

            // delete file if under /storage/
            if (is_string($row->photo_url) && str_starts_with($row->photo_url, '/storage/')) {
                $relative = substr($row->photo_url, strlen('/storage/'));
                \Illuminate\Support\Facades\Storage::disk('public')->delete($relative);
            }

            DB::table('property_photos')->where('photo_id', $photo_id)->delete();

            return back();
        }
}