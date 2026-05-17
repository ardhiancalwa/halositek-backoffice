<?php

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

afterEach(function () {
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

describe('Admin Management - Super Admin Only', function () {
    it('super admin can list all admins', function () {
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'Super Admin', 'email' => 'super@halositek.com']);
        $admin1 = User::factory()->admin()->create(['name' => 'Admin One', 'email' => 'admin1@halositek.com']);
        $admin2 = User::factory()->admin()->create(['name' => 'Admin Two', 'email' => 'admin2@halositek.com']);
        $regularUser = User::factory()->create(['name' => 'Regular User', 'email' => 'user@halositek.com']);

        actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/admins')
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonStructure([
                'success',
                'status_code',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'role', 'account_status'],
                ],
                'meta',
                'links',
            ]);
    });

    it('super admin can search admins by name', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->admin()->create(['name' => 'John Admin', 'email' => 'john@halositek.com']);
        User::factory()->admin()->create(['name' => 'Jane Admin', 'email' => 'jane@halositek.com']);

        actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/admins?search=John')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'John Admin');
    });

    it('super admin can create a new admin', function () {
        $superAdmin = User::factory()->superAdmin()->create();

        actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/admins', [
                'name' => 'New Admin',
                'email' => 'newadmin@halositek.com',
                'password' => 'SecurePassword123!',
                'role' => 'admin',
            ])
            ->assertCreated()
            ->assertJsonPath('data.admin.role', 'admin')
            ->assertJsonPath('data.admin.name', 'New Admin');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@halositek.com',
            'role' => 'admin',
        ]);
    });

    it('super admin can create another super admin', function () {
        $superAdmin = User::factory()->superAdmin()->create();

        actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/admins', [
                'name' => 'Another Super Admin',
                'email' => 'anothersuperadmin@halositek.com',
                'password' => 'SecurePassword123!',
                'role' => 'super_admin',
            ])
            ->assertCreated()
            ->assertJsonPath('data.admin.role', 'super_admin');
    });

    it('super admin can view admin details', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create(['name' => 'Admin User', 'email' => 'admin@halositek.com']);

        actingAs($superAdmin, 'sanctum')
            ->getJson("/api/v1/admins/{$admin->id}")
            ->assertOk()
            ->assertJsonPath('data.admin.id', $admin->id)
            ->assertJsonPath('data.admin.name', 'Admin User');
    });

    it('super admin can update admin role', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/admins/{$admin->id}", [
                'role' => 'super_admin',
            ])
            ->assertOk()
            ->assertJsonPath('data.admin.role', 'super_admin');

        $admin->refresh();
        $this->assertEquals('super_admin', $admin->role->value);
    });

    it('super admin can update admin account status', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create(['account_status' => AccountStatus::Active]);

        actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/admins/{$admin->id}", [
                'account_status' => 'suspend',
            ])
            ->assertOk()
            ->assertJsonPath('data.admin.account_status', 'suspend');
    });

    it('super admin cannot change their own role', function () {
        $superAdmin = User::factory()->superAdmin()->create();

        actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/admins/{$superAdmin->id}", [
                'role' => 'admin',
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'You cannot change your own role.');
    });

    it('super admin can delete another admin', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        actingAs($superAdmin, 'sanctum')
            ->deleteJson("/api/v1/admins/{$admin->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Admin deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    });

    it('super admin cannot delete themselves', function () {
        $superAdmin = User::factory()->superAdmin()->create();

        actingAs($superAdmin, 'sanctum')
            ->deleteJson("/api/v1/admins/{$superAdmin->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'You cannot delete your own admin account.');
    });
});

describe('Admin Management - Access Control', function () {
    it('regular admin cannot list admins', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admins')
            ->assertForbidden();
    });

    it('regular admin cannot create admin', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admins', [
                'name' => 'New Admin',
                'email' => 'newadmin@halositek.com',
                'password' => 'SecurePassword123!',
                'role' => 'admin',
            ])
            ->assertForbidden();
    });

    it('regular admin cannot view admin details', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        actingAs($admin, 'sanctum')
            ->getJson("/api/v1/admins/{$otherAdmin->id}")
            ->assertForbidden();
    });

    it('regular admin cannot update another admin', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admins/{$otherAdmin->id}", [
                'account_status' => 'suspend',
            ])
            ->assertForbidden();
    });

    it('regular admin cannot delete other admin', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admins/{$otherAdmin->id}")
            ->assertForbidden();
    });

    it('regular user cannot access admin management', function () {
        $user = User::factory()->create();

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/admins')
            ->assertForbidden();
    });

    it('unauthenticated user cannot access admin management', function () {
        getJson('/api/v1/admins')
            ->assertUnauthorized();
    });
});

describe('Admin Management - Validation', function () {
    it('creates admin with validation for required fields', function () {
        $superAdmin = User::factory()->superAdmin()->create();

        actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/admins', [])
            ->assertUnprocessable();
    });

    it('creates admin with email uniqueness validation', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $existing = User::factory()->create(['email' => 'duplicate@halositek.com']);

        actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/admins', [
                'name' => 'New Admin',
                'email' => 'duplicate@halositek.com',
                'password' => 'SecurePassword123!',
                'role' => 'admin',
            ])
            ->assertUnprocessable();
    });

    it('creates admin with role validation', function () {
        $superAdmin = User::factory()->superAdmin()->create();

        actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/admins', [
                'name' => 'New Admin',
                'email' => 'newadmin@halositek.com',
                'password' => 'SecurePassword123!',
                'role' => 'invalid_role',
            ])
            ->assertUnprocessable();
    });

    it('updates admin with valid role values', function () {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();

        actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/admins/{$admin->id}", [
                'role' => 'invalid_role',
            ])
            ->assertUnprocessable();
    });
});
