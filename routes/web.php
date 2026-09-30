<?php

use App\Http\Controllers\Admin\DirectoryController;
use App\Http\Controllers\Admin\UserReportController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\MingleController;
use App\Http\Controllers\MyMinglesController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Models\City;
use App\Models\Interest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'cities' => City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(),
    ]);
});

Route::redirect('/features', '/', 302)->name('features');

Route::get('/cities/{city:code}', [CityController::class, 'show'])->name('cities.show');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

$memberMiddleware = config('auth.email_verification_required')
    ? ['auth', 'verified']
    : ['auth'];

Route::middleware($memberMiddleware)->group(function () {
    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        if (! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::get('/discover', [DiscoverController::class, 'index'])->name('discover.index');
    Route::post('/mingles/{mingle}/join', [DiscoverController::class, 'join'])->name('mingles.join');
    Route::get('/my-mingles', [MyMinglesController::class, 'index'])->name('my-mingles.index');
    Route::delete('/my-mingles/{mingle}/rsvp', [MyMinglesController::class, 'leave'])->name('my-mingles.leave');
    Route::patch('/my-mingles/{mingle}/reminders', [MyMinglesController::class, 'updateReminder'])->name('my-mingles.reminders.update');

    Route::get('/mingles/create', [MingleController::class, 'create'])->name('mingles.create');
    Route::get('/mingles/events/create', [MingleController::class, 'createEvent'])->name('mingles.events.create');
    Route::get('/mingles/communities/create', [MingleController::class, 'createCommunity'])->name('mingles.communities.create');
    Route::post('/mingles', [MingleController::class, 'store'])->name('mingles.store');
    Route::post('/mingles/{mingle}/updates', [MingleController::class, 'postUpdate'])->name('mingles.updates.store');
    Route::post('/mingles/{mingle}/invites', [MingleController::class, 'invite'])->name('mingles.invites.store');
    Route::post('/mingles/{mingle}/join-requests/{member}/respond', [MingleController::class, 'respondToJoinRequest'])->name('mingles.join-requests.respond');
    Route::post('/mingle-invites/{invite}/decline', [MingleController::class, 'declineInvite'])->name('mingle-invites.decline');

    Route::get('/mingles/{mingle}', [MingleController::class, 'show'])->name('mingles.show');

    Route::get('/members/blocked', [MemberController::class, 'blocked'])->name('members.blocked');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::post('/members/{member}/connect', [MemberController::class, 'connect'])->name('members.connect');
    Route::delete('/members/{member}/connection', [MemberController::class, 'disconnect'])->name('members.disconnect');
    Route::post('/connection-requests/{connection}/respond', [MemberController::class, 'respond'])->name('connections.respond');
    Route::post('/members/{member}/block', [MemberController::class, 'block'])->name('members.block');
    Route::delete('/members/{member}/block', [MemberController::class, 'unblock'])->name('members.unblock');
    Route::post('/members/{member}/report', [MemberController::class, 'report'])->name('members.report');
    Route::get('/messages', [MemberController::class, 'inbox'])->name('messages.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::get('/members/{member}/messages', [MemberController::class, 'messages'])->name('members.messages');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/deactivate', [ProfileController::class, 'deactivate'])->name('profile.deactivate');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');
    Route::get('/reports', [UserReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [UserReportController::class, 'update'])->name('reports.update');
    Route::post('/cities', [DirectoryController::class, 'storeCity'])->name('cities.store');
    Route::patch('/cities/{city}', [DirectoryController::class, 'updateCity'])->name('cities.update');
    Route::delete('/cities/{city}', [DirectoryController::class, 'destroyCity'])->name('cities.destroy');
    Route::post('/interests', [DirectoryController::class, 'storeInterest'])->name('interests.store');
    Route::patch('/interests/{interest}', [DirectoryController::class, 'updateInterest'])->name('interests.update');
    Route::delete('/interests/{interest}', [DirectoryController::class, 'destroyInterest'])->name('interests.destroy');
});

require __DIR__.'/auth.php';
