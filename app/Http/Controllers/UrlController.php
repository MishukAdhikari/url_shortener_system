<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrlRequest;
use App\Models\Url;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $urls = Url::with('user')->latest()->get();
        return view('urls.index', compact('urls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) // Todo: you can use StoreUrlRequest $request can be skipped
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'original_url' => 'required|url|string|max:255',
        ]);

        $this->authorize('create', Url::class);

        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['name'] = Str::ucfirst($request->name);
        $data['original_url'] = $request->original_url;
        $data['short_url'] = Str::random(7);
        Url::create($data);
        return redirect(route('urls.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Url $url)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Url $url)
    {
        $this->authorize('update', $url);

        return view('urls.edit', ['url' => $url,]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Url $url) // Todo: UpdateUrlRequest $request - not created yet
    {
        $this->authorize('update', $url);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'original_url' => 'required|string|max:255',
        ]);
        $validated['short_url'] = Str::random(7);
        $url->update($validated);
        return redirect(route('urls.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Url $url)
    {
        $this->authorize('delete', $url);

        $url->delete();
        return redirect(route('urls.index'));
    }

    //Redirect to original url from short url
    public function shorten_url($short_url)
    {
        $find = Url::where('short_url', $short_url)->first(); // Todo: Added index on migration, where you add where you must add index

        if($find) {
            $find->incrementAccessCount();
            return redirect($find->original_url);
        }
    }
}
