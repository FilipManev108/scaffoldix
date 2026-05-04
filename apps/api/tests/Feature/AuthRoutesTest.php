<?php

it('exposes public auth endpoints', function (string $method, string $uri, string $message) {
    $this->json($method, $uri)
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => $message,
            'data' => null,
        ]);
})->with([
    ['POST', '/api/register', 'Registration endpoint is available'],
    ['POST', '/api/login', 'Login endpoint is available'],
]);

it('protects authenticated auth endpoints with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['POST', '/api/logout'],
    ['GET', '/api/me'],
]);
