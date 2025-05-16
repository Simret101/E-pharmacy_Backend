<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGenerator
{
    protected $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function generateUsername(string $name): string
    {
        // Convert name to lowercase and remove special characters
        $baseUsername = strtolower(Str::slug($name));
        
        // If name is too long, use first name only
        if (strlen($baseUsername) > 15) {
            $parts = explode('-', $baseUsername);
            $baseUsername = $parts[0];
        }

        // If username is too short, add random characters
        if (strlen($baseUsername) < 3) {
            $baseUsername .= Str::random(3 - strlen($baseUsername));
        }

        $originalUsername = $baseUsername;
        $counter = 1;

        // Check for duplicate usernames and append number if needed
        while ($this->userModel->where('username', $baseUsername)->exists()) {
            $baseUsername = $originalUsername . $counter;
            $counter++;
        }

        return $baseUsername;
    }
}
