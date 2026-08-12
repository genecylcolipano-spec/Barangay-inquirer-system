<?php

namespace App\Http\Controllers;

use App\Services\PasswordStrengthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PasswordStrengthController extends Controller
{
    /**
     * Check password strength
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $password = $request->input('password');
        $service = new PasswordStrengthService();

        return response()->json([
            'status' => $service->isStrong($password) ? 'strong' : 'weak',
            'strength' => $service->getStrengthStatus($password),
            'level' => $service->getStrengthLevel($password),
            'color' => $service->getStrengthColor($password),
        ]);
    }

    /**
     * Get password requirements
     */
    public function requirements(): JsonResponse
    {
        $service = new PasswordStrengthService();

        return response()->json([
            'requirements' => $service->getRequirements(),
        ]);
    }
}
