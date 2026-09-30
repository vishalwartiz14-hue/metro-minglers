<?php

use App\Models\User;

test('discover page renders the interest-first interface for a signed-in member', function () {
    $user = User::factory()->create();

    $response = $this->withoutVite()
        ->actingAs($user)
        ->get(route('discover.index'));

    $response->assertOk()
        ->assertSee('Filter Results')
        ->assertSee('Change Interests')
        ->assertSee('Select Your Interests');
});
