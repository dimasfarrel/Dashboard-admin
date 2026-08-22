<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PublicBookingController extends Controller
{
    public function checkout(Room $room)
    {
        if ($room->status !== 'available' || !$room->is_published) {
            return redirect()->route('public.rooms.index')->with('error', 'Kamar tidak tersedia untuk disewa.');
        }

        return view('public.booking.checkout', compact('room'));
    }

    public function store(Request $request, Room $room)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'duration_months' => 'required|integer|min:1|max:12',
            'nik' => 'required|string|size:16',
            'phone_wa' => 'required|string|min:10',
            'gender' => 'required|in:laki-laki,perempuan',
            'payment_proof' => 'required|image|max:2048'
        ]);

        $totalAmount = $room->price * $request->duration_months;

        $booking = new Booking();
        $booking->user_id = Auth::id();
        $booking->room_id = $room->id;
        $booking->nik = $request->nik;
        $booking->phone_wa = $request->phone_wa;
        $booking->gender = $request->gender;
        $booking->start_date = $request->start_date;
        $booking->duration_months = $request->duration_months;
        $booking->total_amount = $totalAmount;
        
        if ($request->hasFile('payment_proof')) {
            $booking->payment_proof = $request->file('payment_proof')->store('payments', 'public');
        }

        $booking->save();

        return redirect()->route('public.tenant.dashboard')->with('success', 'Pemesanan berhasil! Menunggu verifikasi admin.');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $bookings = Booking::with('room')->where('user_id', $user->id)->latest()->get();
        return view('public.tenant.dashboard', compact('user', 'bookings'));
    }
}
