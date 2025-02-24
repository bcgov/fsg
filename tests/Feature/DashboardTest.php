<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_student_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Student]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/applications');

        $response->assertStatus(200);
    }

    public function test_institution_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Institution_USER]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/institution');

        $response->assertStatus(200);
    }

    public function test_ministry_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Ministry_USER]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/ministry');

        $response->assertStatus(200);
    }
}
