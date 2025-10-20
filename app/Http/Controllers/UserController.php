<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Return all users except admins
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->get();

        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Admin not found'], 404);
        }
        return response()->json($user);
    }

    public function update(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'Admin not found'], 404);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username,' . $id,
        'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($request->hasFile('profile_image')) {
        $image = $request->file('profile_image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('uploads/profile_images'), $filename);
        $validated['profile_image'] = $filename;
    }

    $user->update($validated);
    $user->refresh();

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $user,
    ], 200);
}


}
