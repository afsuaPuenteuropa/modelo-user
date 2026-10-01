<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the first ten users paginated', function () {
    User::factory()->count(12)->create();

    $response = $this->getJson('/api/users');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.email', User::query()->orderBy('id')->first()->email)
        ->assertJsonCount(10, 'data');
});

it('creates a user in the database', function () {
    $payload = [
        'name' => 'Alice Example',
        'email' => 'alice@example.com',
        'password' => 'Secret123',
    ];

    $response = $this->postJson('/api/users/create', $payload);

    $response
        ->assertStatus(201)
        ->assertJsonPath('data.email', 'alice@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'alice@example.com',
        'name' => 'Alice Example',
    ]);
});

it('logs in an existing user with email and password', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('secret-password'),
    ]);

    $response = $this->postJson('/api/users/login', [
        'email' => 'login@example.com',
        'password' => 'secret-password',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', 'login@example.com');
});

it('updates the username and email with the current credentials', function () {
    $user = User::factory()->create([
        'email' => 'update@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->putJson('/api/users/update-username', [
        'email' => 'update@example.com',
        'password' => 'password123',
        'name' => 'Updated Name',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');

    $this->putJson('/api/users/update-email', [
        'email' => 'update@example.com',
        'password' => 'password123',
        'new_email' => 'new-email@example.com',
    ])->assertOk()
        ->assertJsonPath('data.email', 'new-email@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'new-email@example.com',
        'name' => 'Updated Name',
    ]);
});

it('updates the password and removes the user with the current credentials', function () {
    $user = User::factory()->create([
        'email' => 'delete@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->putJson('/api/users/update-password', [
        'email' => 'delete@example.com',
        'password' => 'password123',
        'new_password' => 'newPassword456',
    ])->assertOk();

    $this->assertTrue(
        app('hash')->check('newPassword456', User::find($user->id)->password)
    );

    $this->deleteJson('/api/users/delete', [
        'email' => 'delete@example.com',
        'password' => 'newPassword456',
    ])->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
