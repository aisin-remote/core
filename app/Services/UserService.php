<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Get all users with optional search and company filter
     */
    public function getAllUsers($search = null, $company = null)
    {
        return User::with('employee')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($q) use ($search) {
                            $q->where('npk', 'like', "%{$search}%");
                        });
                });
            })
            ->when($company, function ($query) use ($company) {
                $query->whereHas('employee', function ($q) use ($company) {
                    $q->where('company_name', $company);
                });
            })
            ->paginate(10);
    }

    /**
     * Find a user by ID
     */
    public function findUser($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data)
    {
        $data['password'] = bcrypt($data['password']);
        return User::create($data);
    }

    /**
     * Update an existing user
     */
    public function updateUser($id, array $data)
    {
        $user = User::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
            $user->is_first_login = 1;
            $user->password_changed_at = null;
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $user;
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}
