<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesTableSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
        
        $this->user = User::factory()->create();
    }

    /** @test */
    public function an_admin_can_assign_a_role_to_a_user()
    {
        $token = auth()->tokenById($this->admin->id);
        // Acting as an Admin
        $role = Role::create(['name' => 'User' , 'guard_name' => 'api']);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson(route('users.assignRole', ['id' => $this->user->id]), [
            'role' => $role->name
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Role assigned successfully',
            'user' => [
                'id' => $this->user->id,
                'roles' => [
                    [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                        'created_at' => $role->created_at->toJSON(),
                        'updated_at' => $role->updated_at->toJSON(),
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function it_fails_if_assigning_a_nonexistent_role()
    {
        // Acting as an Admin
        $token = auth()->tokenById($this->admin->id);

        // Acting as an Admin with an invalid role name
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson(route('users.assignRole', $this->user->id), [
            'role' => 'NonExistentRole'
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors('role');
    }

    /** @test */
    public function an_admin_can_remove_a_role_from_a_user()
    {
        // Assign role first
        $token = auth()->tokenById($this->admin->id);
        $this->user->assignRole('Vendor');
        

        // Acting as an Admin to remove the role
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson(route('users.removeRole', $this->user->id), [
            'role' => 'Vendor'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Role removed successfully',
        ]);

        // Check if the role was removed
        $this->assertFalse($this->user->hasRole('User'));
    }

    /** @test */
    public function it_fails_if_removing_a_role_the_user_does_not_have()
    {
        $token = auth()->tokenById($this->admin->id);

        // Acting as an Admin to remove a role that doesn't exist on the user
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson(route('users.removeRole', $this->user->id), [
            'role' => 'Vendor'
        ]);

        $response->assertStatus(400); // Bad request
        $response->assertJson([
            'message' => 'User does not have the specified role',
        ]);
    }

    /** @test */
    public function a_user_can_get_their_roles()
    {
        $token = auth()->tokenById($this->user->id);
        // Assign role to the user
        $this->user->assignRole('Vendor');

        // Acting as the user to fetch their roles
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson(route('users.getUserRoles'));
        $response->assertStatus(200);
        $response->assertJson([
            'roles' => ['Vendor'],
        ]);
    }

    /** @test */
    public function non_admin_users_cannot_remove_roles()
    {
        $token = auth()->tokenById($this->user->id);

        // Acting as a regular user trying to remove a role from the admin
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson(route('users.removeRole', $this->admin->id), [
            'role' => 'Admin'
        ]);
    

        $response->assertStatus(403); // Forbidden
    }
}
