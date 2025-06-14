<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\DateShowtime;
use App\Models\Movie;
use App\Models\Date;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Notifications\BookingConfirmation;
use App\Notifications\BookingCancellation;

class BookingController extends Controller
{
    /**
     * Create returns the booking page.
     *
     * @param Movie $movie
     * @param Date $date
     * @param Showtime $showtime
     * @return View|RedirectResponse
     */
    public function create(Movie $movie, Date $date, Showtime $showtime): View|RedirectResponse
    {
        $user = User::find(auth()->id());

        // check if the user is old enough to watch the movie
        if ($user->age < $movie->age_rating) {
            return back()
                ->with('error', 'You are not old enough to watch this movie!');
        }

        $currentDate = today('Asia/Jakarta')->format('Y-m-d');
        $currentTime = now('Asia/Jakarta')->format('H:i:s');

        // date formatting
        $formattedDate = $date->date->format('Y-m-d');
        $isToday = $formattedDate == $currentDate;
        $isPastDate = $formattedDate < $currentDate;
        $isPastShowtime = $showtime->start_time < $currentTime;

        // check if the date and showtime is in the past of the current date and time
        if ($isPastDate || ($isToday && $isPastShowtime)) {
            return back()
                ->with('error', 'Cannot book tickets for past dates and showtimes!');
        }

        $seats = Seat::all();

        return view('bookings.create', compact('movie', 'date', 'showtime', 'seats'));
    }

    /**
     * Store the booking.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'seats' => ['required', 'array', 'min:1', 'max:6', 'exists:seats,id'],
        ]);

        $user = User::find(auth()->id());
        $movie = Movie::find($request->movie);
        $date = Date::find($request->date);
        $showtime = Showtime::find($request->showtime);
        $seats = Seat::find($request->seats);
        $date_showtime = DateShowtime::where('date_id', $date->id)
            ->where('showtime_id', $showtime->id)
            ->first();

        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->movie_id = $movie->id;
        $booking->date_showtime_id = $date_showtime->id;
        $booking->total_price = count($request->seats) * $movie->ticket_price;
        $booking->status = BookingStatus::PAID;

        // check if the user has enough balance to book the tickets
        if ($user->balance < $booking->total_price) {
            return back()
                ->with('error', 'You do not have enough balance to book these tickets!');
        }

        $user->balance -= $booking->total_price;

        $user->update();
        $booking->save();

        foreach ($seats as $seat) {
            $booking->seats()->attach($seat->id, ['date_showtime_id' => $date_showtime->id]);
        }

        // Send booking confirmation notification
        $user->notify(new BookingConfirmation($booking));

        return redirect()
            ->route('home')
            ->with('success', 'Successfully booked tickets!');
    }

    /**
     * Index returns the booking history page.
     *
     * @return View
     */
    public function index(): View
    {
        $user = User::find(auth()->id());
        $bookings = Booking::where('user_id', $user->id)
            ->with('movie', 'dateShowtime.date', 'dateShowtime.showtime', 'seats')
            ->latest()
            ->paginate(5);

        $currentDate = today('Asia/Jakarta')->format('Y-m-d');
        $currentTime = now('Asia/Jakarta')->format('H:i:s');

        return view('bookings.index', compact('bookings', 'currentDate', 'currentTime'));
    }

    /**
     * Update the booking status.
     *
     * @param Booking $booking
     * @return RedirectResponse
     */
    public function update(Booking $booking): RedirectResponse
    {
        $booking->status = BookingStatus::CANCELLED;
        $booking->update();

        foreach ($booking->seats as $seat) {
            $booking->seats()->detach($seat->id);
        }

        $user = User::find(auth()->id());
        $user->balance += $booking->total_price;
        $user->update();

        // Send booking cancellation notification
        $user->notify(new BookingCancellation($booking));

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking cancelled!');
    }
}
