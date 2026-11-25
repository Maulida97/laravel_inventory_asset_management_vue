<?php

use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    $department = Department::factory()->create();

    $this->superAdmin = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $this->superAdmin->assignRole('Super Admin');

    $this->admin = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $this->admin->assignRole('Admin');

    $this->requester = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $this->requester->assignRole('Requester');

    $this->inventoryStaff = User::factory()->create([
        'department_id' => $department->id,
        'is_active' => true,
        'registration_status' => 'approved',
    ]);
    $this->inventoryStaff->assignRole('Inventory Staff');
});

/*
|--------------------------------------------------------------------------
| Happy Path Tests
|--------------------------------------------------------------------------
*/

describe('Happy Path: Category Management', function () {
    test('authorized user can view category index page with stats and pagination', function () {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('categories.index'));

        $response->assertOk();
    });

    test('authorized user can create a new category', function () {
        $payload = [
            'code' => 'cat-atk',
            'name' => 'Alat Tulis Kantor',
            'type' => 'inventory',
            'description' => 'Kategori untuk seluruh ATK operasional kantor',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('categories.store'), $payload);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('categories', [
            'code' => 'CAT-ATK',
            'name' => 'Alat Tulis Kantor',
            'type' => 'inventory',
            'is_active' => 1,
        ]);
    });

    test('authorized user can update an existing category', function () {
        $category = Category::factory()->create([
            'code' => 'CAT-ELEK',
            'name' => 'Elektronik Lama',
            'type' => 'both',
        ]);

        $payload = [
            'code' => 'cat-elek',
            'name' => 'Elektronik & Gadget Baru',
            'type' => 'asset',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category), $payload);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'code' => 'CAT-ELEK',
            'name' => 'Elektronik & Gadget Baru',
            'type' => 'asset',
        ]);
    });

    test('authorized user can toggle category active status', function () {
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->patch(route('categories.toggle-status', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('status');
        $this->assertFalse($category->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($this->admin)->patch(route('categories.toggle-status', $category));
        $this->assertTrue($category->fresh()->is_active);
    });

    test('authorized user can delete category if unused', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    });
});

/*
|--------------------------------------------------------------------------
| Evil Path (Security) Tests
|--------------------------------------------------------------------------
*/

describe('Evil Path: Security, Authentication & Authorization', function () {
    test('guest (unauthenticated) cannot access category index and is redirected to login', function () {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    });

    test('guest (unauthenticated) cannot create category', function () {
        $response = $this->post(route('categories.store'), [
            'code' => 'CAT-HACK',
            'name' => 'Unauthorized Category',
            'type' => 'inventory',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('categories', ['code' => 'CAT-HACK']);
    });

    test('unauthorized role (Requester) is forbidden (403) from accessing category index', function () {
        $response = $this->actingAs($this->requester)->get(route('categories.index'));

        $response->assertForbidden();
    });

    test('unauthorized role (Inventory Staff) is forbidden (403) from creating category', function () {
        $response = $this->actingAs($this->inventoryStaff)->post(route('categories.store'), [
            'code' => 'CAT-STAFF',
            'name' => 'Staff Category',
            'type' => 'inventory',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('categories', ['code' => 'CAT-STAFF']);
    });

    test('unauthorized role (Requester) is forbidden (403) from updating category', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->requester)->put(route('categories.update', $category), [
            'code' => 'CAT-MOD',
            'name' => 'Modified by Requester',
            'type' => 'both',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('categories', ['name' => 'Modified by Requester']);
    });

    test('unauthorized role (Inventory Staff) is forbidden (403) from toggling category status', function () {
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->inventoryStaff)->patch(route('categories.toggle-status', $category));

        $response->assertForbidden();
        $this->assertTrue($category->fresh()->is_active);
    });

    test('unauthorized role (Requester) is forbidden (403) from deleting category', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->requester)->delete(route('categories.destroy', $category));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    });

    test('validation rejects duplicate category code', function () {
        Category::factory()->create(['code' => 'CAT-DUP']);

        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'code' => 'CAT-DUP',
            'name' => 'Duplicate Code Category',
            'type' => 'inventory',
        ]);

        $response->assertSessionHasErrors(['code']);
    });

    test('validation rejects invalid category type', function () {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'code' => 'CAT-TEST',
            'name' => 'Invalid Type',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors(['type']);
    });
});
