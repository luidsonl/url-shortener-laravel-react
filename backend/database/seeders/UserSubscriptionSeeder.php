<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use App\Models\UserSubscription;
use App\Enums\SubscriptionStatus;
use Carbon\Carbon;

class UserSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $plans = Plan::all();

        if ($users->isEmpty() || $plans->isEmpty()) {
            $this->command->warn('No users or plans found. Please run the User and Plan seeders first.');
            return;
        }

        foreach ($users as $index => $user) {
            $plan = $plans[$index % $plans->count()];

            UserSubscription::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
                [
                    'status' => $this->getRandomStatus(),
                    'expires_at' => Carbon::now()->addMonths(rand(1, 12)),
                ]
            );
        }

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $plan = $plans->first();

            UserSubscription::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'plan_id' => $plan->id,
                ],
                [
                    'status' => SubscriptionStatus::ACTIVE,
                    'expires_at' => Carbon::now()->addYear(),
                ]
            );
        }

        $this->command->info('User subscriptions seeded successfully.');
    }

    private function getRandomStatus(): SubscriptionStatus
    {
        $statuses = [
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::PENDING,
            SubscriptionStatus::CANCELED,
            SubscriptionStatus::EXPIRED,
        ];

        return $statuses[array_rand($statuses)];
    }
}
