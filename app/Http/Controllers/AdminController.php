<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard(): View
    {
        $totalMovies = Movie::count();
        $totalBookings = Booking::count();
        $totalUsers = User::where('role', 'user')->count();
        $recentBookings = Booking::with(['user', 'movie'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalMovies',
            'totalBookings',
            'totalUsers',
            'recentBookings'
        ));
    }

    /**
     * Show movie management page
     */
    public function movies(): View
    {
        $movies = Movie::latest()->paginate(10);
        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Show booking management page
     */
    public function bookings(): View
    {
        $bookings = Booking::with(['user', 'movie'])
            ->latest()
            ->paginate(10);
        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show user management page
     */
    public function users(): View
    {
        $users = User::where('role', 'user')
            ->latest()
            ->paginate(10);
        return view('admin.users.index', compact('users'));
    }
} 