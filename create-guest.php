<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Company;

// Create or get the single guest account
$guest = User::firstOrCreate(
    ['email' => 'guest@getaxia.de'],
    [
        'first_name' => 'Guest',
        'last_name' => 'User',
        'is_guest' => true,
        'password' => bcrypt('guest-not-allowed-to-login'),
        'n8n_webhook_url' => 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d',
        'chart_webhook_url' => 'https://n8n.getaxia.de/webhook/c3352634-be98-4448-903a-d04ed64ea90b',
    ]
);

echo "Guest account: {$guest->email} (ID: " . substr($guest->id, 0, 8) . ")\n";

// Create company for guest if not exists
if (!$guest->companies()->exists()) {
    Company::create(['owner_user_id' => $guest->id]);
    echo "Created company for guest\n";
}

// Show stats
$total = User::count();
$guests = User::where('is_guest', true)->count();
$withEmail = User::whereNotNull('email')->where('email', '!=', '')->count();

echo "\nTotal users: {$total}\n";
echo "Guest accounts: {$guests} (should be 1)\n";
echo "Users with email: {$withEmail}\n";
