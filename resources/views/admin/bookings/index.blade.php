@extends('layouts.app')

@section('content')
    <h1>Bookings List</h1>
    <table class="table-auto w-full">
        <thead>
            <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">User</th>
                <th class="px-4 py-2">Movie</th>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Showtime</th>
                <th class="px-4 py-2">Seats</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td class="border px-4 py-2">{{ $booking->id }}</td>
                <td class="border px-4 py-2">{{ $booking->user->name ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $booking->movie->title ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $booking->date->date ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $booking->showtime->start_time ?? '-' }} - {{ $booking->showtime->end_time ?? '-' }}</td>
                <td class="border px-4 py-2">
                    @foreach($booking->seats as $seat)
                        {{ $seat->seat_number }}@if(!$loop->last), @endif
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection 