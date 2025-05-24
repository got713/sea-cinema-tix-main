<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminMovieController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show the form for creating a new movie.
     */
    public function create(): View
    {
        return view('admin.movies.create');
    }

    /**
     * Store a newly created movie in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:movies',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'poster_url' => 'required|url',
            'age_rating' => 'required|integer|min:0|max:18',
            'ticket_price' => 'required|integer|min:0',
            'trailer_url' => 'nullable|url',
            'genre' => 'required|string|max:255',
        ]);

        Movie::create($validated);

        return redirect()
            ->route('admin.movies')
            ->with('success', 'Movie created successfully.');
    }

    /**
     * Show the form for editing the specified movie.
     */
    public function edit(Movie $movie): View
    {
        return view('admin.movies.edit', compact('movie'));
    }

    /**
     * Update the specified movie in storage.
     */
    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:movies,title,' . $movie->id,
            'description' => 'required|string',
            'release_date' => 'required|date',
            'poster_url' => 'required|url',
            'age_rating' => 'required|integer|min:0|max:18',
            'ticket_price' => 'required|integer|min:0',
            'trailer_url' => 'nullable|url',
            'genre' => 'required|string|max:255',
        ]);

        $movie->update($validated);

        return redirect()
            ->route('admin.movies')
            ->with('success', 'Movie updated successfully.');
    }

    /**
     * Remove the specified movie from storage.
     */
    public function destroy(Movie $movie): RedirectResponse
    {
        $movie->delete();

        return redirect()
            ->route('admin.movies')
            ->with('success', 'Movie deleted successfully.');
    }
} 