<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $featuredRooms = Room::with('facilities')
            ->where('is_published', true)
            ->where('status', 'available')
            ->take(3)
            ->get();
            
        return view('public.home', compact('featuredRooms'));
    }

    public function rooms(Request $request)
    {
        $query = Room::with(['facilities', 'images'])
            ->where('is_published', true)
            ->where('status', 'available'); // Selalu tampilkan yang tersedia saja
        
        if ($request->filled('q')) {
            $query->where('room_number', 'like', '%' . $request->q . '%');
        }
        
        $rooms = $query->paginate(9);
        
        return view('public.rooms.index', compact('rooms'));
    }

    public function showRoom(Room $room)
    {
        if (!$room->is_published) {
            abort(404);
        }
        
        $room->load(['facilities', 'images']);
        return view('public.rooms.show', compact('room'));
    }
}
