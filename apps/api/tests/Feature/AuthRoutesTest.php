<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a valid user', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Example User',
        'email' => 'register@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'name' => 'Example User',
                    'email' => 'register@example.com',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Example User',
        'email' => 'register@example.com',
    ]);
});

it('exposes the public login endpoint', function () {
    $this->postJson('/api/login', [
        'email' => 'auth-route-login@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Login endpoint is available',
            'data' => null,
        ]);
});

it('returns validation errors for invalid registration payloads', function () {
    $this->postJson('/api/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
});

it('rejects duplicate email registration', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $this->postJson('/api/register', [
        'name' => 'Duplicate User',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
        ]);
});

it('hashes registered user passwords', function () {
    $this->postJson('/api/register', [
        'name' => 'Password User',
        'email' => 'password@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertCreated();

    $user = User::where('email', 'password@example.com')->firstOrFail();

    expect($user->password)
        ->not->toBe('password')
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

it('does not return sensitive user fields after registration', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Safe User',
        'email' => 'safe@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated();

    expect($response->json('data.user'))
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

it('returns validation errors for invalid login payloads', function () {
    $this->postJson('/api/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'password',
        ]);
});

it('protects authenticated auth endpoints with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['POST', '/api/logout'],
    ['GET', '/api/me'],
]);
