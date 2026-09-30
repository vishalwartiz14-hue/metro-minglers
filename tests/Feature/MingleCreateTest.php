<?php

use App\Models\City;
use App\Models\Interest;
use App\Models\Mingle;
use App\Models\User;

function activeCity(): City
{
    return City::create([
        'name' => 'Metro City',
        'code' => 'MC',
        'is_active' => true,
    ]);
}

test('guest cannot access mingle creation', function () {
    $this->get('/mingles/create')->assertRedirect('/login');
});

test('create page is displayed for authenticated users', function () {
    $response = $this
        ->actingAs(User::factory()->create())
        ->get('/mingles/create');

    $response
        ->assertOk()
        ->assertSee('CREATE A MINGLE')
        ->assertSee('Event Mingle')
        ->assertSee('Community Mingle')
        ->assertSee('Food & Drinks');
});

test('welcome city links open a public city directory', function () {
    $city = activeCity();

    $this->get(route('cities.show', $city))
        ->assertOk()
        ->assertSee("What's happening in Metro City", false)
        ->assertSee('New connections are coming soon.');
});

test('welcome page reads only active cities from the database', function () {
    $activeCity = activeCity();
    City::create([
        'name' => 'Inactive City',
        'code' => 'OFF',
        'is_active' => false,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee($activeCity->name)
        ->assertDontSee('Inactive City');
});

test('city directory carries its city id into registration', function () {
    $city = activeCity();

    $this->get(route('register', ['city_id' => $city->id]))
        ->assertOk()
        ->assertViewHas('selectedCityId', $city->id);
});

test('event and community each have a dedicated creation route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mingles.events.create'))
        ->assertOk()
        ->assertSee('type: "event"', false);

    $this->actingAs($user)
        ->get(route('mingles.communities.create'))
        ->assertOk()
        ->assertSee('type: "community"', false);
});

test('event mingle can be created', function () {
    $user = User::factory()->create();
    $city = activeCity();

    $response = $this->actingAs($user)->post('/mingles', [
        'type' => 'event',
        'title' => 'Sunset Brunch',
        'description' => 'A relaxed brunch by the river.',
        'city_id' => $city->id,
        'category' => 'Food & Drinks',
        'visibility' => 'open',
        'date' => now()->addWeek()->toDateString(),
        'start_time' => '11:00',
        'end_time' => '13:00',
        'venue' => 'River Cafe',
        'address_details' => 'Pier 4',
        'maximum_attendees' => 20,
        'allow_guests' => '1',
    ]);

    $mingle = Mingle::first();

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('mingles.show', $mingle));

    expect($mingle)
        ->type->toBe('event')
        ->city_id->toBe($city->id)
        ->venue->toBe('River Cafe')
        ->starts_at->not->toBeNull()
        ->ends_at->not->toBeNull()
        ->allow_guests->toBeTrue();

    expect($mingle->fresh()->cityRecord->name)->toBe('Metro City');

    expect($mingle->attendees->pluck('id')->all())
        ->toContain($user->id);
});

test('community mingle can be created without event fields', function () {
    $user = User::factory()->create();
    $city = activeCity();

    $response = $this->actingAs($user)->post('/mingles', [
        'type' => 'community',
        'title' => 'Urban Hikers',
        'description' => 'Weekly city hikes.',
        'city_id' => $city->id,
        'category' => 'Outdoors & Adventure',
        'visibility' => 'public',
        'tags' => 'hiking, outdoors',
        'members_can_create_events' => '1',
    ]);

    $mingle = Mingle::first();

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('mingles.show', $mingle));

    expect($mingle)
        ->type->toBe('community')
        ->visibility->toBe('public')
        ->tags->toBe(['hiking', 'outdoors'])
        ->starts_at->toBeNull()
        ->venue->toBeNull()
        ->members_can_create_events->toBeTrue();
});

test('event requires date, start time and venue', function () {
    $city = activeCity();

    $this->actingAs(User::factory()->create())
        ->from('/mingles/create')
        ->post('/mingles', [
            'type' => 'event',
            'title' => 'Missing details',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'open',
        ])
        ->assertSessionHasErrors(['date', 'start_time', 'venue'])
        ->assertRedirect('/mingles/create');
});

test('event scheduled earlier today is rejected', function () {
    $city = activeCity();

    $this->actingAs(User::factory()->create())
        ->post('/mingles', [
            'type' => 'event',
            'title' => 'Already happened',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'open',
            'date' => now()->toDateString(),
            'start_time' => '00:00',
            'venue' => 'Old Hall',
        ])
        ->assertSessionHasErrors('start_time');
});

test('description is optional', function () {
    $city = activeCity();

    $this->actingAs(User::factory()->create())
        ->post('/mingles', [
            'type' => 'community',
            'title' => 'No description needed',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'public',
        ])
        ->assertSessionHasNoErrors();

    expect(Mingle::first()->description)->toBeNull();
});

test('category must exist in the interests table', function () {
    $city = activeCity();

    $this->actingAs(User::factory()->create())
        ->post('/mingles', [
            'type' => 'community',
            'title' => 'Bad category',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Not A Real Category',
            'visibility' => 'public',
        ])
        ->assertSessionHasErrors('category');
});

test('inactive interests are rejected', function () {
    $city = activeCity();

    Interest::create([
        'name' => 'Retired Hobby',
        'is_active' => false,
        'sort_order' => 99,
    ]);

    $this->actingAs(User::factory()->create())
        ->post('/mingles', [
            'type' => 'community',
            'title' => 'Retired topic',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Retired Hobby',
            'visibility' => 'public',
        ])
        ->assertSessionHasErrors('category');
});

test('event cannot use community visibility and vice versa', function () {
    $city = activeCity();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/mingles', [
            'type' => 'event',
            'title' => 'Wrong visibility',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'public',
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '10:00',
            'venue' => 'Hall',
        ])
        ->assertSessionHasErrors('visibility');

    $this->actingAs($user)
        ->post('/mingles', [
            'type' => 'community',
            'title' => 'Wrong visibility 2',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'open',
        ])
        ->assertSessionHasErrors('visibility');
});

test('title must be at least three characters', function () {
    $city = activeCity();

    $this->actingAs(User::factory()->create())
        ->post('/mingles', [
            'type' => 'community',
            'title' => 'ab',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'public',
        ])
        ->assertSessionHasErrors('title');
});

test('create page renders old input after a failed submit', function () {
    $user = User::factory()->create();
    $city = activeCity();

    $this->actingAs($user)
        ->from('/mingles/create')
        ->post('/mingles', [
            'type' => 'event',
            'title' => 'Half filled event',
            'description' => 'Still missing the date and venue.',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'open',
        ])
        ->assertSessionHasErrors(['date', 'start_time', 'venue']);

    $this->actingAs($user)
        ->get('/mingles/create')
        ->assertOk()
        ->assertSee('Half filled event')
        ->assertSee('Please select an event date.');
});

test('inactive cities are rejected', function () {
    $city = City::create([
        'name' => 'Hidden City',
        'code' => 'HC',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->post('/mingles', [
            'type' => 'community',
            'title' => 'Nowhere',
            'description' => '...',
            'city_id' => $city->id,
            'category' => 'Travel',
            'visibility' => 'public',
        ])
        ->assertSessionHasErrors('city_id');
});

test('mingle show page is displayed after creation', function () {
    $user = User::factory()->create();
    $city = activeCity();

    $this->actingAs($user)->post('/mingles', [
        'type' => 'event',
        'title' => 'Boat Day',
        'description' => 'Out on the lake.',
        'city_id' => $city->id,
        'category' => 'Outdoors & Adventure',
        'visibility' => 'connections',
        'date' => now()->addDays(3)->toDateString(),
        'start_time' => '09:30',
        'venue' => 'Marina Dock',
    ]);

    $this->actingAs($user)
        ->get(route('mingles.show', Mingle::first()))
        ->assertOk()
        ->assertSee('Boat Day')
        ->assertSee($city->name);
});
