<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $cityId = \App\Models\City::query()->where('code', 'CLT')->value('id');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'gender' => 'prefer_not_to_say',
            'city_id' => $cityId,
            'zip_code' => '28202',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('onboarding.show', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'city_id' => $cityId,
    ]);
});
