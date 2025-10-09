<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAdminController extends Controller
{
    public function createAdmin()
    {
        // Create admin user every time
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => '$2y$12$GStIlJ9lRodi6mdib15oku3QrOBP1hu5WsJ6nd2v5R1VT3bqv.9PW',
                'role' => 'admin',
            ]
        );

        session()->flash('info', 'Admin created successfully!');

        // Redirect back (or to a page of your choice)
        return redirect()->back();
    }
}
