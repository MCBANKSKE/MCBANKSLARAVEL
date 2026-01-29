<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'role:create {name : The name of the role}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new role';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roleName = strtolower($this->argument('name'));

        if (Role::where('name', $roleName)->exists()) {
            $this->error("Role '{$roleName}' already exists.");
            return Command::FAILURE;
        }

        Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $this->info("Role '{$roleName}' created successfully.");

        return Command::SUCCESS;
    }
}
