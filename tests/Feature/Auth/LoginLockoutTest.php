<?php

use App\Models\User;

/**
 * @param  array<string, string>  $credentials
 */
function attemptLoginTimes(int $times, array $credentials): void
{
    foreach (range(1, $times) as $attempt) {
        test()->post(route('login.store'), $credentials);
    }
}

test('five failed attempts block new attempts for 30 minutes (NFR-006)', function () {
    $user = User::factory()->create();
    $wrongCredentials = ['email' => $user->email, 'password' => 'wrong-password'];

    attemptLoginTimes(5, $wrongCredentials);

    $this->travel(29)->minutes();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertTooManyRequests();
    $this->assertGuest();
});

test('the login is accepted again once the 30 minutes pass (NFR-006)', function () {
    $user = User::factory()->create();

    attemptLoginTimes(5, ['email' => $user->email, 'password' => 'wrong-password']);

    $this->travel(31)->minutes();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
});
