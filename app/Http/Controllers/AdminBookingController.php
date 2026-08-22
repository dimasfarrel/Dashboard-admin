<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tenant;
use App\Models\Lodging;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'room'])->latest()->get();
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'room']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking is not pending.');
        }

        DB::beginTransaction();
        try {
            // Update booking status
            $booking->update(['status' => 'approved']);

            // Update room status
            $room = $booking->room;
            $room->update(['status' => 'occupied']);

            // Find or create tenant based on NIK
            $tenant = Tenant::firstOrCreate(
                ['nik' => $booking->nik],
                [
                    'name' => $booking->user->name,
                    'phone_wa' => $booking->phone_wa,
                    'gender' => $booking->gender,
                    'room_id' => $room->id,
                    'start_date' => $booking->start_date,
                    'status' => 'active',
                ]
            );
            
            // Ensure tenant is assigned to the booked room if they already exist
            if ($tenant->room_id !== $room->id) {
                $tenant->update(['room_id' => $room->id]);
            }

            // Create Lodging
            $startDate = Carbon::parse($booking->start_date);
            $endDate = $startDate->copy()->addMonths($booking->duration_months);
            $lodging = Lodging::create([
                'room_id'        => $room->id,
                'pic_name'       => $booking->user->name,
                'pic_phone'      => $booking->phone_wa,
                'pic_nik'        => $booking->nik,
                'check_in'       => $startDate->startOfDay(),
                'check_out'      => $endDate->startOfDay(),
                'duration_days'  => $booking->duration_months * 30,
                'guest_count'    => 1,
                'price_per_night'=> round($booking->total_amount / max($booking->duration_months * 30, 1), 2),
                'total_price'    => $booking->total_amount,
                'payment_status' => 'paid',
                'paid_at'        => now(),
                'status'         => 'active',
            ]);

            // Create Payment
            Payment::create([
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'amount' => $booking->total_amount,
                'payment_date' => now(),
                'period_month' => Carbon::parse($booking->start_date)->month,
                'period_year' => Carbon::parse($booking->start_date)->year,
                'status' => 'paid'
            ]);

            DB::commit();
            return redirect()->route('admin.bookings.index')->with('success', 'Booking approved and lodging created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving booking: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking is not pending.');
        }

        $booking->update(['status' => 'rejected']);
        return redirect()->route('admin.bookings.index')->with('success', 'Booking rejected.');
    }
}
