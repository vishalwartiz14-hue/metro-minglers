<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Mingle;
use Illuminate\View\View;

class CityController extends Controller
{
    /** Show public events and communities available in a city. */
    public function show(City $city): View
    {
        abort_unless($city->is_active, 404);

        $mingles    =   Mingle::query()->with('host')->withCount('attendees')->where('city_id', $city->id)
            ->publiclyVisible()
            ->where(function ($query): void {
                $query->where('type', 'community')
                    ->orWhere('starts_at', '>=', now());
            }) ->orderByRaw("case when type = 'event' then 0 else 1 end")
            ->orderBy('starts_at')->latest() ->get();

        return view('cities.show', compact('city', 'mingles'));
    }
}