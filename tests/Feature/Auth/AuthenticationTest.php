<?php

namespace Tests\Feature\Auth;

use App\Models\Allocation;
use App\Models\Institution;
use App\Models\InstitutionStaff;
use App\Models\ProgramYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_ministry_admin_can_access(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Ministry_ADMIN]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/ministry/');
        $response->assertStatus(200);
    }

    public function test_institution_user_can_access(): void
    {
        $user = User::factory()->create();
        $institution = Institution::factory()->create();
        $institutionStaff = InstitutionStaff::factory()->create(['institution_guid' => $institution->guid, 'user_guid' => $user->guid]);
        $programYear = ProgramYear::factory()->create();
        Allocation::factory()->create(['institution_guid' => $institution->guid, 'program_year_guid' => $programYear->guid]);


        $role = Role::firstOrCreate(['name' => Role::Institution_USER]);
        $user->roles()->attach($role->id);
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);
        $response = $this->get('/institution/dashboard');
        $response->assertStatus(200)
        ->assertSee("Future Skills Grant");
    }

    public function test_institution_user_no_bceid_redirect(): void
    {
        $user = User::factory()->create(['bceid_user_guid' => null]);
        $institution = Institution::factory()->create();
        $institutionStaff = InstitutionStaff::factory()->create(['institution_guid' => $institution->guid, 'user_guid' => $user->guid]);
        $programYear = ProgramYear::factory()->create();
        Allocation::factory()->create(['institution_guid' => $institution->guid, 'program_year_guid' => $programYear->guid]);


        $role = Role::firstOrCreate(['name' => Role::Institution_USER]);
        $user->roles()->attach($role->id);
        $response = $this->actingAs($user)->get('/institution/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_ministry_guest_cannot_access(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Ministry_GUEST]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/ministry/');

        $this->assertTrue($user->roles->contains('name', Role::Ministry_GUEST));
        $response->assertRedirect('/login');
    }

    public function test_institution_cannot_access_ministry(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Institution_USER]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/ministry/');
        $response->assertRedirect('/login');
    }

    public function test_institution_guest_cannot_access(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Institution_GUEST]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/institution/dashboard');
        $response->assertRedirect('/login');
    }
}
