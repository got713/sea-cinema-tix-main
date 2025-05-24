<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {username} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (User::where('username', $username)->exists()) {
            $this->error('Username already exists!');
            return 1;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists!');
            return 1;
        }

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'name' => $username,
            'role' => 'admin',
            'age' => 18,
            'balance' => 0,
        ]);

        $this->info('Admin user created successfully!');
        return 0;
    }
} 