<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;
use App\Http\Controllers\Admin\RoleController;
use ReflectionMethod;

class FixPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates all missing granular permissions and assigns them to the admin, super_admin, and principle roles.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permission fix...');

        // 1. Get all grouped permissions by instantiating the controller (it creates them if missing)
        $controller = app(RoleController::class);
        $reflection = new ReflectionMethod($controller, 'getGroupedPermissions');
        $reflection->setAccessible(true);
        $groupedPermissions = $reflection->invoke($controller);

        // Collect all permission IDs and names
        $allPermissionIds = [];
        $validPermissionNames = [];
        foreach ($groupedPermissions as $group => $perms) {
            foreach ($perms as $perm) {
                $allPermissionIds[] = $perm->id;
                $validPermissionNames[] = $perm->name;
            }
        }
        
        // Delete orphaned permissions (like the commented-out certificates)
        $deleted = Permission::whereNotIn('name', $validPermissionNames)->delete();
        if ($deleted > 0) {
            $this->info("Deleted {$deleted} unused/orphaned permissions (e.g. certificates).");
        }
        
        $this->info('Total active permissions found/created: ' . count($allPermissionIds));

        // 2. Assign ALL permissions to Admin, Super Admin, and Principle
        $rolesToUpdate = Role::whereIn('name', ['admin', 'super_admin', 'principle', 'principal', 'vice_principal'])->get();
        
        foreach ($rolesToUpdate as $role) {
            $role->permissions()->sync($allPermissionIds);
            $this->info("Assigned all " . count($allPermissionIds) . " permissions to role: " . $role->name);
        }

        $this->info('Permissions successfully fixed and assigned!');
    }
}
