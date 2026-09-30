<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $selectedCityId = City::query()
            ->whereKey($request->integer('city_id'))
            ->where('is_active', true)
            ->value('id');

        return view('auth.register', [
            'cities' => City::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedCityId' => $selectedCityId,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $verificationRequired = config('auth.email_verification_required');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'gender' => ['required', 'in:woman,man,non_binary,prefer_not_to_say'],
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where('is_active', true),
            ],
            'zip_code' => ['required', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'city_id' => $request->city_id,
            'zip_code' => $request->zip_code,
            'email_verified_at' => $verificationRequired ? null : now(),
        ]);

        Auth::login($user);

        if ($verificationRequired) {
            event(new Registered($user));
        }

        return redirect()->route('onboarding.show');
    }
}
