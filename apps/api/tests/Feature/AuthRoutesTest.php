<?php

it('exposes public auth endpoints', function (string $method, string $uri, array $payload, string $message) {
    $this->json($method, $uri, $payload)
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => $message,
            'data' => null,
        ]);
})->with([
    [
        'POST',
        '/api/register',
        [
            'name' => 'Example User',
            'email' => 'auth-route-register@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ],
        'Registration endpoint is available',
    ],
    [
        'POST',
        '/api/login',
        [
            'email' => 'auth-route-login@example.com',
            'password' => 'password',
        ],
        'Login endpoint is available',
    ],
]);

it('returns validation errors for invalid registration payloads', function () {
    $this->postJson('/api/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
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
