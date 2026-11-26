<?php

use App\Models\User;

test('a deactivated account cannot log in (UC-018 A2)', function () {
    $user = User::factory()->inactive()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors(['email' => 'Estas credenciales no coinciden con nuestros registros.']);

    $this->assertGuest();
});

test('the session of an account deactivated after logging in is ended (UC-018 A2)', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
