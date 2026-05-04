<?php

it('returns a healthy API response', function () {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'API is running',
        ])
        ->assertJsonStructure([
            'data' => [
                'app',
                'environment',
            ],
        ]);
});
