<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--email= : Admin email} {--name= : Display name} {--password= : Password}';
    protected $description = 'Create an admin user for the Filament admin panel at /admin';

    public function handle(): int
    {
        $email    = $this->option('email')    ?? $this->ask('Email');
        $name     = $this->option('name')     ?? $this->ask('Name', 'Rack Rake Admin');
        $password = $this->option('password') ?? $this->secret('Password');

        if (Admin::where('email', $email)->exists()) {
            $this->error("An admin with email {$email} already exists.");
            return self::FAILURE;
        }

        Admin::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin created: {$name} <{$email}>");
        $this->line('Log in at /admin');

        return self::SUCCESS;
    }
}
