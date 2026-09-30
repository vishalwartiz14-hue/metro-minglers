<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public const REASONS = [
        'meet_people' => 'Meet new people',
        'find_activities' => 'Find activities and events',
        'go_out_more' => 'Go out more in my city',
        'travel' => 'Find travel companions',
        'network' => 'Build professional connections',
        'dating' => 'Explore dating (optional)',
    ];

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->onboarding_completed_at) {
            return config('auth.email_verification_required') && ! $request->user()->hasVerifiedEmail()
                ? redirect()->route('verification.notice')
                : redirect()->route('dashboard');
        }

        return view('onboarding', [
            'interests' => Interest::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'reasons' => self::REASONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'interest_ids' => ['required', 'array', 'min:1', 'max:14'],
            'interest_ids.*' => [
                'integer',
                Rule::exists('interests', 'id')->where('is_active', true),
            ],
            'joining_reasons' => ['required', 'array', 'min:1', 'max:6'],
            'joining_reasons.*' => ['required', 'string', Rule::in(array_keys(self::REASONS))],
            'dating_mode' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $user->interestOptions()->sync(array_unique($data['interest_ids']));
        $user->forceFill([
            'joining_reasons' => array_values(array_unique($data['joining_reasons'])),
            'dating_mode' => $request->boolean('dating_mode'),
            'onboarding_completed_at' => now(),
        ])->save();

        return config('auth.email_verification_required') && ! $user->hasVerifiedEmail()
            ? redirect()->route('verification.notice')->with('status', 'verification-link-sent')
            : redirect()->route('dashboard')->with('status', 'Welcome to MetroMinglers!');
    }
}
