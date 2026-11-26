<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a user with the initial password is sent to change it before any other page (UC-018 BR-005)', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('initial-password.edit'));

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertRedirect(route('initial-password.edit'));
});

test('the change initial password page is rendered for a user with the initial password', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('initial-password.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/change-initial-password'));
});

test('a user with the initial password can still log out', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
});

test('the initial password is replaced and the user continues to the dashboard', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->put(route('initial-password.update'), [
            'password' => 'nueva-clave-segura',
            'password_confirmation' => 'nueva-clave-segura',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->must_change_password)->toBeFalse()
        ->and(Hash::check('nueva-clave-segura', $user->password))->toBeTrue();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('the new password must differ from the initial password (OQ-042)', function () {
    $user = User::factory()->mustChangePassword()->create(['password' => 'clave-inicial']);

    $this->actingAs($user)
        ->from(route('initial-password.edit'))
        ->put(route('initial-password.update'), [
            'password' => 'clave-inicial',
            'password_confirmation' => 'clave-inicial',
        ])
        ->assertSessionHasErrors(['password' => 'La nueva contraseña debe ser distinta de la contraseña inicial.'])
        ->assertRedirect(route('initial-password.edit'));

    expect($user->refresh()->must_change_password)->toBeTrue();
});

test('the new password needs at least 8 characters and its confirmation (OQ-042)', function (array $input, string $message) {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->put(route('initial-password.update'), $input)
        ->assertSessionHasErrors(['password' => $message]);

    expect($user->refresh()->must_change_password)->toBeTrue();
})->with([
    'shorter than 8' => [
        ['password' => 'corta1', 'password_confirmation' => 'corta1'],
        'El campo contraseña debe tener al menos 8 caracteres.',
    ],
    'not confirmed' => [
        ['password' => 'nueva-clave-segura', 'password_confirmation' => 'otra-clave-segura'],
        'La confirmación del campo contraseña no coincide.',
    ],
]);

test('a user without the initial password is sent from the change page to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('initial-password.edit'))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($user)
        ->put(route('initial-password.update'), [
            'password' => 'nueva-clave-segura',
            'password_confirmation' => 'nueva-clave-segura',
        ])
        ->assertForbidden();
});
