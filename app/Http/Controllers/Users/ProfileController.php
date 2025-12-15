<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:50',
            'last_name'  => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user->name = (string) $request->input('name');
        $user->first_name = $request->input('first_name');
        $user->last_name  = $request->input('last_name');
        $user->email = (string) $request->input('email');

        if ($request->filled('password')) {
            $user->password = Hash::make((string) $request->input('password'));
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');

            $ext = strtolower((string) $file->getClientOriginalExtension());
            if ($ext === '') {
                $ext = 'png';
            }

            $disk = $user->profilePhotoDisk();

            // Folder: profiles/{user_id}
            $folder = 'profiles/' . $user->id;

            // 1) Delete everything currently in that folder (safe delete)
            try {
                // deleteDirectory is safe for both local and S3-like disks
                Storage::disk($disk)->deleteDirectory($folder);
            } catch (\Throwable $e) {
                // Non-fatal: continue to attempt upload
            }

            // 2) Store the new file as profiles/{id}/{id}.{ext}
            $filename = $user->id . '.' . $ext;

            try {
                // putFileAs works consistently across local and S3 disks.
                // Pass 'public' so the file is readable if the disk supports ACL/visibility.
                $path = Storage::disk($disk)->putFileAs($folder, $file, $filename, 'public');
            } catch (\Throwable $e) {
                return back()->withErrors(['profile_photo' => 'Failed to upload profile photo. Please try again.']);
            }

            if (!$path) {
                return back()->withErrors(['profile_photo' => 'Failed to upload profile photo. Please try again.']);
            }

            // 3) Save the key to DB (putFileAs returns the stored path)
            $user->profile_photo_path = $path;
        }


        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        $user->delete();
        Auth::logout();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
