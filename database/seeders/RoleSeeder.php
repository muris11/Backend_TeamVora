<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Spatie Roles — names MUST match users.role column values (B0-1 fix)
        $adminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $leadRole = Role::firstOrCreate(['name' => 'team_leader']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        // Permissions
        $features = [
            'view_dashboard',
            'view_cash_book', 'write_cash_book',
            'view_split_bill', 'write_split_bill', 'pay_split_bill', 'verify_split_bill',
            'view_recurring_bill', 'write_recurring_bill',
            'view_tasks', 'write_tasks', 'update_task_status',
            'view_daily_log', 'write_daily_log',
            'view_media', 'write_media',
            'view_blog', 'write_blog', 'manage_blog',
            'manage_rbac', 'manage_teams', 'manage_env', 'manage_email_templates',
            'manage_members', 'manage_permissions',
        ];

        foreach ($features as $feature) {
            Permission::firstOrCreate(['name' => $feature]);
        }

        $adminRole->syncPermissions(Permission::all());

        $leadRole->syncPermissions([
            'view_dashboard',
            'view_cash_book', 'write_cash_book',
            'view_split_bill', 'write_split_bill', 'verify_split_bill',
            'view_recurring_bill', 'write_recurring_bill',
            'view_tasks', 'write_tasks', 'update_task_status',
            'view_daily_log', 'write_daily_log',
            'view_media', 'write_media',
            'view_blog', 'write_blog', 'manage_blog',
            'manage_teams', 'manage_members', 'manage_permissions',
        ]);

        $memberRole->syncPermissions([
            'view_dashboard',
            'view_cash_book', 'write_cash_book',
            'view_split_bill', 'write_split_bill', 'pay_split_bill',
            'view_tasks', 'update_task_status',
            'view_daily_log', 'write_daily_log',
            'view_media', 'write_media',
            'view_blog',
        ]);



        // Super Admin (Owner)
        $admin = User::where('email', 'admin@teamvora.local')->first();
        if (! $admin) {
            $admin = User::factory()->create([
                'name' => 'Super Admin',
                'email' => 'admin@teamvora.local',
                'password' => bcrypt('password'),
            ]);
        }
        $admin->update(['role' => 'super_admin']);
        $admin->syncRoles([$adminRole]);

        // Create demo team
        $team = Team::firstOrCreate(
            ['slug' => 'tim-alpha'],
            ['name' => 'Tim Alpha', 'description' => 'Tim demonstrasi TeamVora']
        );

        // Team Leader
        $lead = User::where('email', 'lead@teamvora.local')->first();
        if (! $lead) {
            $lead = User::factory()->create([
                'name' => 'Budi (Leader)',
                'email' => 'lead@teamvora.local',
                'password' => bcrypt('password'),
            ]);
        }
        $lead->update(['role' => 'team_leader', 'team_id' => $team->id]);
        $lead->syncRoles([$leadRole]);
        $team->update(['leader_id' => $lead->id]);

        // Team Member
        $member = User::where('email', 'member@teamvora.local')->first();
        if (! $member) {
            $member = User::factory()->create([
                'name' => 'Andi (Member)',
                'email' => 'member@teamvora.local',
                'password' => bcrypt('password'),
            ]);
        }
        $member->update(['role' => 'member', 'team_id' => $team->id]);
        $member->syncRoles([$memberRole]);
    }
}
