<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        $countries = Country::withCount('channels')->orderBy('name')->paginate(20);

        return view('countries.index', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'flag' => 'nullable|string|max:10',
        ]);

        Country::create($data);

        return redirect()->route('countries.index')->with('success', 'Country added.');
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $country->id . ',id',
            'flag' => 'nullable|string|max:10',
        ]);

        $country->update($data);

        return redirect()->route('countries.index')->with('success', 'Country updated.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->route('countries.index')->with('success', 'Country deleted.');
    }
}