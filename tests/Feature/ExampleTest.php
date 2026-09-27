<?php

declare(strict_types=1);

use App\Models\User;

test('the root route redirects guest directly to login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('the portal route redirects directly to login page', function () {
    $response = $this->get('/portal');

    $response->assertRedirect('/login');
});

test('the root route redirects authenticated user to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect('/dashboard');
});
