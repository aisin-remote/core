<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class PasswordService
{
    protected $user;

    /**
     * Create a new service instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function changePassword($user, $data, $isFirstLogin)
    {
        DB::beginTransaction();

        try {
            // Validate input
            $validation = $this->validatePasswordData($data, $isFirstLogin);
            if (!$validation['success']) {
                DB::rollBack();
                return $validation;
            }

            // Verify current password (except for first login)
            if (!$isFirstLogin && !$this->verifyCurrentPassword($user, $data['current_password'])) {
                DB::rollBack();
                return [
                    'success' => false,
                    'errors' => ['current_password' => 'Current password does not match']
                ];
            }

            // Update password
            $this->updateUserPassword($user, $data['new_password']);

            DB::commit();

            return ['success' => true];

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error if needed
            logger()->error('Password change failed: ' . $e->getMessage());

            return [
                'success' => false,
                'errors' => ['system' => 'A system error occurred. Please try again.']
            ];
        }
    }

    protected function validatePasswordData($data, $isFirstLogin)
    {
        $rules = [
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // at least 1 uppercase
                'regex:/[a-z]/',      // at least 1 lowercase
                'regex:/[0-9]/',      // at least 1 number
                'regex:/[@$!%*#?&^()\-_+=<>:;",.\/~\\[\]{}|`]/', // at least 1 special char
                'confirmed'
            ]
        ];

        if (!$isFirstLogin) {
            $rules['current_password'] = 'required';
        }

        $messages = [
            'new_password.regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'current_password.required' => 'Current password is required.'
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        return ['success' => true];
    }

    protected function verifyCurrentPassword($user, $currentPassword)
    {
        return Hash::check($currentPassword, $user->password);
    }

    protected function updateUserPassword($user, $newPassword)
    {
        $user->password = Hash::make($newPassword);
        $user->is_first_login = false;
        $user->password_changed_at = now();
        $user->save();
    }
}
