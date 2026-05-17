<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Resolves or creates a customer from name + phone.
 *
 * Lookup strategy:
 *   1. Find existing user by phone number
 *   2. If not found, create a new user (role=customer) + customer_profile
 *
 * This is intentionally lenient — the AI agent may not have a customer_id,
 * so we use phone as the unique identifier.
 */
class CustomerResolverService
{
    public function resolveOrCreate(string $name, string $phone): User
    {
        $existingUser = User::where('phone', $phone)->first();

        if ($existingUser !== null) {
            return $existingUser;
        }

        return DB::transaction(function () use ($name, $phone) {
            $nameParts = $this->splitName($name);

            $user = User::create([
                'role' => UserRole::Customer,
                'first_name' => $nameParts['first'],
                'last_name' => $nameParts['last'],
                'phone' => $phone,
                'status' => UserStatus::Active,
                'password' => null, // AI-created customers authenticate via OTP/phone
            ]);

            CustomerProfile::create([
                'user_id' => $user->id,
                'loyalty_points' => 0,
            ]);

            return $user;
        });
    }

    /**
     * @return array{first: string, last: string}
     */
    private function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [
            'first' => $parts[0],
            'last' => $parts[1] ?? '',
        ];
    }
}
