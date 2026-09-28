<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Complete matrix of permissions grouped by module with authorized roles.
     * Roles:
     * - SA    : Super Admin
     * - ADM   : Admin
     * - INV   : Inventory Staff
     * - ASSET : Asset Staff
     * - REQ   : Requester
     * - MGR   : Manager
     */
    public const PERMISSIONS_MATRIX = [
        // 1. LOCATION MANAGEMENT
        'location.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'location.create' => ['Super Admin', 'Admin'],
        'location.edit' => ['Super Admin', 'Admin'],
        'location.toggle-status' => ['Super Admin', 'Admin'],
        'location.move-parent' => ['Super Admin', 'Admin'],

        // 2. ITEM MASTER
        'item.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'item.view-detail' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'item.create' => ['Super Admin', 'Admin'],
        'item.edit' => ['Super Admin', 'Admin'],
        'item.toggle-status' => ['Super Admin', 'Admin'],
        'category.manage' => ['Super Admin', 'Admin'],
        'unit.manage' => ['Super Admin', 'Admin'],

        // 3. INVENTORY — STOCK IN
        'inventory.po.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.po.submit' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.gr.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.direct-receipt.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.po.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'inventory.gr.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],

        // 4. INVENTORY — STOCK OUT
        'inventory.internal-usage.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.stock-request.create' => ['Super Admin', 'Admin', 'Inventory Staff', 'Requester'],
        'inventory.transfer.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.transfer.confirm' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.disposal.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.stock-out.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Requester', 'Manager'],

        // 5. INVENTORY — STOCK MANAGEMENT
        'inventory.adjustment.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.opname.create' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.opname.input' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.opname.confirm' => ['Super Admin', 'Admin', 'Inventory Staff'],
        'inventory.stock.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'inventory.ledger.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'inventory.transaction.view-all' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],

        // 6. ASSET MANAGEMENT
        'asset.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Manager'],
        'asset.view-detail' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Manager'],
        'asset.create' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.edit' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.assign' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.return' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.mutate' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.maintenance.record' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.maintenance.complete' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.dispose' => ['Super Admin', 'Admin', 'Asset Staff'],
        'asset.report-lost' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff'],
        'asset.assignment-history.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'asset.mutation-history.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'asset.maintenance-history.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],

        // 7. APPROVAL WORKFLOW
        'approval.review.level-1' => ['Super Admin', 'Admin'],
        'approval.approve.level-1' => ['Super Admin', 'Admin'],
        'approval.review.level-2' => ['Super Admin', 'Manager'],
        'approval.approve.level-2' => ['Super Admin', 'Manager'],
        'approval.my-request.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'approval.my-request.cancel' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'approval.my-request.resubmit' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'approval.configure' => ['Super Admin'],
        'approval.user-registration' => ['Super Admin'],

        // 8. USER & ROLE MANAGEMENT
        'user.view' => ['Super Admin', 'Admin'],
        'user.create' => ['Super Admin', 'Admin'],
        'user.edit' => ['Super Admin', 'Admin'],
        'user.assign-role' => ['Super Admin'],
        'user.toggle-status' => ['Super Admin', 'Admin'],
        'user.approve-registration' => ['Super Admin'],
        'department.manage' => ['Super Admin', 'Admin'],
        'supplier.manage' => ['Super Admin'],
        'profile.edit' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'profile.change-password' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'profile.forgot-password' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],

        // 9. REPORTING
        'report.stock.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Manager'],
        'report.stock-mutation.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.stock-in.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.stock-out.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.stock-request.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Requester', 'Manager'],
        'report.stock-opname.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.stock-adjustment.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.stock-minimum.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.po.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'report.asset.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.asset-location.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.asset-employee.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.asset-mutation.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.asset-assignment.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.asset-booking.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.maintenance.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.maintenance-cost.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.disposal.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.lost-asset.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.vendor-warranty.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.internal-warranty.view' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'report.handover-form.print' => ['Super Admin', 'Admin', 'Asset Staff'],
        'report.export' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Manager'],

        // 10. AUDIT LOG
        'audit.view' => ['Super Admin', 'Admin'],
        'audit.filter' => ['Super Admin', 'Admin'],
        'audit.view-detail' => ['Super Admin', 'Admin'],

        // 11. DASHBOARD
        'dashboard.view' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'dashboard.widget.total-assets' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'dashboard.widget.stock-minimum' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'dashboard.widget.expiry-alert' => ['Super Admin', 'Admin', 'Inventory Staff', 'Manager'],
        'dashboard.widget.approval-pending' => ['Super Admin', 'Admin', 'Manager'],
        'dashboard.widget.approval-overdue' => ['Super Admin', 'Admin', 'Manager'],
        'dashboard.widget.vendor-warranty' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'dashboard.widget.internal-warranty' => ['Super Admin', 'Admin', 'Asset Staff', 'Manager'],
        'dashboard.widget.recent-transactions' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Manager'],
        'dashboard.widget.my-submissions' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],
        'dashboard.widget.my-requests' => ['Requester'],
        'dashboard.widget.notifications' => ['Super Admin', 'Admin', 'Inventory Staff', 'Asset Staff', 'Requester', 'Manager'],

        // 12. SYSTEM CONFIGURATION
        'config.approval.manage' => ['Super Admin'],
        'config.telescope.view' => ['Super Admin'],
        'config.login-rate-limit.manage' => ['Super Admin'],
        'import.items' => ['Super Admin', 'Admin'],
        'import.assets' => ['Super Admin', 'Admin'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure RoleSeeder has run
        $this->call(RoleSeeder::class);

        DB::transaction(function () {
            // 1. Create all permissions
            $createdPermissions = [];
            foreach (self::PERMISSIONS_MATRIX as $permissionName => $roles) {
                $createdPermissions[$permissionName] = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }

            // 2. Map permissions to roles
            $rolePermissionsMap = [
                'Super Admin' => [],
                'Admin' => [],
                'Inventory Staff' => [],
                'Asset Staff' => [],
                'Requester' => [],
                'Manager' => [],
            ];

            foreach (self::PERMISSIONS_MATRIX as $permissionName => $assignedRoles) {
                foreach ($assignedRoles as $roleName) {
                    if (isset($rolePermissionsMap[$roleName])) {
                        $rolePermissionsMap[$roleName][] = $permissionName;
                    }
                }
            }

            // 3. Sync permissions for each role
            foreach ($rolePermissionsMap as $roleName => $permissions) {
                $role = Role::findByName($roleName, 'web');
                if ($roleName === 'Super Admin') {
                    // Super Admin gets all permissions
                    $role->syncPermissions(array_keys(self::PERMISSIONS_MATRIX));
                } else {
                    $role->syncPermissions($permissions);
                }
            }
        });

        // Clear cache again after seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
