<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

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

it('sends a verification email to newly registered users', function () {
    Notification::fake();

    $this->postJson('/api/register', [
        'name' => 'Verify User',
        'email' => 'verify-register@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertCreated();

    $user = User::where('email', 'verify-register@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('logs in a valid user', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logged in successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'login@example.com',
                ],
            ],
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

it('rejects invalid login credentials', function () {
    User::factory()->create([
        'email' => 'invalid-login@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'invalid-login@example.com',
        'password' => 'wrong-password',
    ])
        ->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

it('rejects disabled user login attempts', function () {
    User::factory()->create([
        'email' => 'disabled-login@example.com',
        'disabled_at' => now(),
    ]);

    $this->postJson('/api/login', [
        'email' => 'disabled-login@example.com',
        'password' => 'password',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Account is disabled',
            'errors' => [
                'account' => ['This account has been disabled.'],
            ],
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

it('returns the authenticated user from me', function () {
    $user = User::factory()->create([
        'email' => 'me@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'me@example.com',
        'password' => 'password',
    ])->assertOk();

    $this->getJson('/api/me')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Authenticated user retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'me@example.com',
                ],
            ],
        ])
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonMissingPath('data.user.remember_token');
});

it('allows an authenticated unverified user to request verification resend', function () {
    Notification::fake();

    User::factory()->unverified()->create([
        'email' => 'resend@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'resend@example.com',
        'password' => 'password',
    ])->assertOk();

    $this->postJson('/api/email/verification-notification')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Verification email sent',
            'data' => null,
        ]);

    $user = User::where('email', 'resend@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('marks a user email as verified from a valid verification link', function () {
    $user = User::factory()->unverified()->create();

    $this->getJson(signedVerificationUrlFor($user))
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => null,
        ]);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects invalid verification links', function () {
    $user = User::factory()->unverified()->create();

    $this->getJson(signedVerificationUrlFor($user, sha1('invalid-email')))
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Invalid verification link',
            'errors' => [
                'verification' => ['The email verification link is invalid or has expired.'],
            ],
        ]);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('returns an appropriate response when email is already verified', function () {
    $user = User::factory()->create();

    $this->getJson(signedVerificationUrlFor($user))
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Email is already verified',
            'data' => null,
        ]);
});

it('blocks disabled authenticated users from me', function () {
    $user = User::factory()->create([
        'email' => 'disabled-me@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'disabled-me@example.com',
        'password' => 'password',
    ])->assertOk();

    $user->forceFill([
        'disabled_at' => now(),
    ])->save();

    $this->getJson('/api/me')
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Account is disabled',
            'errors' => [
                'account' => ['This account has been disabled.'],
            ],
        ]);
});

it('protects authenticated auth endpoints with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['POST', '/api/logout'],
    ['GET', '/api/me'],
]);

it('prevents unauthenticated users from requesting verification resend', function () {
    $this->postJson('/api/email/verification-notification')
        ->assertUnauthorized();
});

it('logs out an authenticated user', function () {
    User::factory()->create([
        'email' => 'logout@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'logout@example.com',
        'password' => 'password',
    ])->assertOk();

    $this->postJson('/api/logout')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);
});

it('prevents a logged-out user from accessing me', function () {
    User::factory()->create([
        'email' => 'logged-out@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'logged-out@example.com',
        'password' => 'password',
    ])->assertOk();

    $this->getJson('/api/me')->assertOk();

    $this->postJson('/api/logout')->assertOk();

    $this->getJson('/api/me')->assertUnauthorized();
});

it('blocks disabled authenticated users from logout', function () {
    $user = User::factory()->create([
        'email' => 'disabled-logout@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'disabled-logout@example.com',
        'password' => 'password',
    ])->assertOk();

    $user->forceFill([
        'disabled_at' => now(),
    ])->save();

    $this->postJson('/api/logout')
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Account is disabled',
            'errors' => [
                'account' => ['This account has been disabled.'],
            ],
        ]);
});

function signedVerificationUrlFor(User $user, ?string $hash = null): string
{
    return URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]
    );
}
