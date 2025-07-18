<?php

namespace App\Http\Controllers;

use App\Services\PasswordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    protected $passwordService;

    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    public function showChangeForm()
    {
        return view('website.auth.changePassword', [
            'isFirstLogin' => Auth::user()->is_first_login
        ]);
    }

    public function changeForm(Request $request)
    {
        $user = Auth::user();
        $isFirstLogin = $user->is_first_login;

        try {
            $result = $this->passwordService->changePassword(
                $user,
                $request->all(),
                $isFirstLogin
            );

            if ($result['success']) {
                return back()->with('success', 'Password changed successfully');
            }

            return back()->withErrors($result['errors']);

        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'A system error occurred. Please try again.']);
        }
    }
}
