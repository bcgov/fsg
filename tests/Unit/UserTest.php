<?php

namespace Tests\Unit;

use App\Models\Role;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_user_with_valid_data(): void
    {
        // Create a user using the factory.
        $user = User::factory()->create();

        // Assert that the user was created.
        $this->assertNotNull($user->id);

        // Assert the GUID is exactly 32 characters long.
        $this->assertEquals(32, strlen($user->guid), 'GUID should be 32 characters long');

        // Assert required attributes are not empty.
        $this->assertNotEmpty($user->first_name);
        $this->assertNotEmpty($user->last_name);
        $this->assertNotEmpty($user->email);

        // Verify the password is hashed (it shouldn't equal the plain text 'password').
        $this->assertNotEquals('password', $user->password);

        // Verify the user exists in the database with the given email.
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);
    }

    #[Test]
    public function it_does_not_allow_duplicate_emails(): void
    {
        // Create a user.
        $user = User::factory()->create();

        // Expect a QueryException when trying to create another user with the same email.
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Attempt to create a second user with a duplicate email.
        User::factory()->create(['email' => $user->email]);
    }

    #[Test]
    public function test_user_is_assigned_super_admin_role(): void
    {
        // Create a user.
        $user = User::factory()->create();

        // Retrieve or create the Super Admin role.
        $role = Role::firstOrCreate(['name' => Role::SUPER_ADMIN]);

        // Attach the role to the user.
        $user->roles()->attach($role->id);

        // Reload the user to get the fresh roles relation.
        $user->load('roles');

        // Assert that the pivot table has the expected record.
        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        // Assert through the relationship that the user has the Super Admin role.
        $this->assertTrue($user->roles->contains('name', Role::SUPER_ADMIN));
    }
}
