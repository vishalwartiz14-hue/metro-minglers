<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\City;
use App\Models\Interest;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View{
        return view('profile.edit', [
            'user'              =>  $request->user()->load('interestOptions', 'cityRecord'),
            'blockedMembers'    =>  $request->user()->blockedUsers()->orderBy('name')->get(),
            'cities'            =>  City::where('is_active', true)->orderBy('name')->get(),
            'interests'         =>  Interest::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse{
        $user                   =   $request->user();
        $data                   =   $request->safe()->except([
            'profile_photo', 'cover_photo', 'interest_ids', 'remove_profile_photo', 'remove_cover_photo', 'joining_reasons_present',
        ]);
        $data['dating_mode'] = $request->boolean('dating_mode');
        if ($request->boolean('joining_reasons_present')) {
            $data['joining_reasons'] = array_values(array_filter($request->input('joining_reasons', [])));
        }
        $emailChanged           =   $user->email !== $data['email'];

        foreach (['profile_photo', 'cover_photo'] as $field) {
            $pathColumn = $field.'_path';

            if ($request->boolean('remove_'.$field) && $user->{$pathColumn}) {
                Storage::disk('public')->delete($user->{$pathColumn});
                $data[$pathColumn] = null;
            }

            if ($request->hasFile($field)) {
                if ($user->{$pathColumn}) {
                    Storage::disk('public')->delete($user->{$pathColumn});
                }
                $data[$pathColumn]      =    $request->file($field)->store('members/'.$user->id, 'public');
            }
        }
        $user->fill($data);
        $verificationRequired = config('auth.email_verification_required');

        if ($emailChanged) {
            $user->email_verified_at = $verificationRequired ? null : now();
        }
        $user->save();
        $user->interestOptions()->sync($request->validated('interest_ids', []));

        if ($emailChanged && $verificationRequired) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function deactivate(Request $request): RedirectResponse{
        $request->validateWithBag('deactivateAccount', ['password' => ['required', 'current_password']]);
        $request->user()->update(['deactivated_at' => now()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/')->with('status', 'Your account has been deactivated.');
    }

    /**
     * Delete the user's account.
     */

    public function destroy(Request $request): RedirectResponse{
        $request->validateWithBag('userDeletion', [
            'password'          =>  ['required', 'current_password'],
        ]);

        $user                   =   $request->user();
        $files = collect([
            $user->profile_photo_path,
            $user->cover_photo_path,
        ])->merge($user->mingles()->pluck('image_path'))->filter()->unique()->all();
        Storage::disk('public')->delete($files);
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }
}
