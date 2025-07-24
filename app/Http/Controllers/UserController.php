<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request, $company = null)
    {
        $search = $request->input('search');
        $users = $this->userService->getAllUsers($search, $company);

        return view('website.master.users.index', compact('users', 'company'));
    }

    public function edit($id)
    {
        $user = $this->userService->findUser($id);
        return view('website.master.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|string',
            'password' => 'nullable|string|min:8',
        ]);

        $user = $this->userService->updateUser($id, $validatedData);

        return redirect()->route('users.master.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $this->userService->deleteUser($id);

        return redirect()->route('users.master.index')
            ->with('success', 'User deleted successfully');
    }
}
