<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Goal;
use App\Models\MissingTodo;
use App\Models\Run;
use App\Models\Todo;
use App\Models\TodoEvaluation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        $user = User::updateOrCreate(
            ['email' => 'info@getaxia.de'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'is_guest' => false,
                'n8n_webhook_url' => 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d',
            ]
        );

        // Create company
        $company = Company::updateOrCreate(
            ['owner_user_id' => $user->id],
            [
                'name' => 'TechStartup GmbH',
                'business_model' => 'b2b_saas',
                'team_cofounders' => 2,
                'team_employees' => 5,
                'user_position' => 'CEO & Co-Founder',
                'customer_profile' => 'Small to medium businesses looking to improve productivity',
                'market_insights' => 'Growing market for AI-powered productivity tools',
            ]
        );

        // Create goals
        $goals = [
            [
                'title' => 'Reach 100 paying customers',
                'description' => 'Our primary growth metric for this quarter',
                'priority' => 'high',
            ],
            [
                'title' => 'Launch mobile app beta',
                'description' => 'Expand to mobile platforms',
                'priority' => 'medium',
            ],
            [
                'title' => 'Reduce churn to under 5%',
                'description' => 'Focus on customer retention',
                'priority' => 'high',
            ],
        ];

        foreach ($goals as $goalData) {
            Goal::updateOrCreate(
                ['company_id' => $company->id, 'title' => $goalData['title']],
                $goalData
            );
        }

        // Create multiple analysis runs
        $runs = [
            [
                'created_at' => now()->subDays(14),
                'overall_score' => 45,
                'summary_text' => 'Your focus could be improved. Many tasks are not aligned with your top goals.',
                'todos' => [
                    ['title' => 'Fix login bug', 'score' => 30],
                    ['title' => 'Update documentation', 'score' => 25],
                    ['title' => 'Team meeting prep', 'score' => 40],
                    ['title' => 'Review customer feedback', 'score' => 65],
                ],
            ],
            [
                'created_at' => now()->subDays(7),
                'overall_score' => 67,
                'summary_text' => 'Good progress! Your task list is becoming more aligned with growth objectives.',
                'todos' => [
                    ['title' => 'Outreach to 10 potential customers', 'score' => 85],
                    ['title' => 'Fix payment integration', 'score' => 70],
                    ['title' => 'Design new onboarding flow', 'score' => 75],
                    ['title' => 'Update social media', 'score' => 35],
                    ['title' => 'Respond to support tickets', 'score' => 55],
                ],
            ],
            [
                'created_at' => now()->subDays(2),
                'overall_score' => 82,
                'summary_text' => 'Excellent focus! Your priorities are well-aligned with reaching 100 paying customers.',
                'todos' => [
                    ['title' => 'Follow up with trial users', 'score' => 90],
                    ['title' => 'Launch email campaign', 'score' => 85],
                    ['title' => 'Partner integration call', 'score' => 80],
                    ['title' => 'Optimize landing page conversion', 'score' => 88],
                    ['title' => 'Schedule investor update', 'score' => 60],
                    ['title' => 'Code review for new feature', 'score' => 75],
                ],
                'missing_todos' => [
                    [
                        'title' => 'Set up referral program',
                        'description' => 'A referral program could significantly accelerate customer acquisition by leveraging your existing customer base.',
                        'impact_score' => 88,
                        'category' => 'prioritization',
                        'suggested_owner_role' => 'Marketing Lead',
                    ],
                    [
                        'title' => 'Create case studies from top customers',
                        'description' => 'Social proof is crucial for B2B SaaS. Case studies help convert prospects by showing real results.',
                        'impact_score' => 82,
                        'category' => 'prioritization',
                        'suggested_owner_role' => 'Sales Lead',
                    ],
                    [
                        'title' => 'Implement product analytics dashboard',
                        'description' => 'Better visibility into product usage helps identify upsell opportunities and reduce churn.',
                        'impact_score' => 75,
                        'category' => 'prioritization',
                        'suggested_owner_role' => 'Product Manager',
                    ],
                ],
            ],
        ];

        foreach ($runs as $runData) {
            $run = Run::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'user_id' => $user->id,
                'overall_score' => $runData['overall_score'],
                'summary_text' => $runData['summary_text'],
                'created_at' => $runData['created_at'],
                'updated_at' => $runData['created_at'],
            ]);

            foreach ($runData['todos'] as $todoData) {
                $todo = Todo::create([
                    'id' => Str::uuid(),
                    'run_id' => $run->id,
                    'raw_input' => $todoData['title'],
                    'normalized_title' => $todoData['title'],
                ]);

                TodoEvaluation::create([
                    'id' => Str::uuid(),
                    'todo_id' => $todo->id,
                    'run_id' => $run->id,
                    'color' => $todoData['score'] >= 70 ? 'green' : ($todoData['score'] >= 50 ? 'yellow' : 'orange'),
                    'score' => $todoData['score'],
                    'reasoning' => $this->generateReasoning($todoData['title'], $todoData['score']),
                    'priority_recommendation' => $todoData['score'] >= 70 ? 'high' : ($todoData['score'] >= 50 ? 'low' : 'none'),
                    'action_recommendation' => $todoData['score'] >= 70 ? 'keep' : ($todoData['score'] >= 50 ? 'delegate' : 'drop'),
                ]);
            }

            // Create missing todos (suggestions) for the latest run
            if (isset($runData['missing_todos'])) {
                $primaryGoal = $company->goals()->where('priority', 'high')->first();
                foreach ($runData['missing_todos'] as $missingData) {
                    MissingTodo::create([
                        'id' => Str::uuid(),
                        'run_id' => $run->id,
                        'goal_id' => $primaryGoal?->id,
                        'title' => $missingData['title'],
                        'description' => $missingData['description'],
                        'impact_score' => $missingData['impact_score'],
                        'category' => $missingData['category'],
                        'suggested_owner_role' => $missingData['suggested_owner_role'],
                    ]);
                }
            }
        }

        $this->command->info('Test user created: test@test.test / passwordtest');
        $this->command->info('Created ' . count($runs) . ' analysis runs with sample data.');
    }

    private function generateReasoning(string $title, int $score): string
    {
        if ($score >= 70) {
            return "This task directly contributes to your primary goal of reaching 100 paying customers. It should be prioritized and completed as soon as possible.";
        } elseif ($score >= 50) {
            return "This task has moderate impact on your business goals. Consider scheduling it for later this week after high-priority items are complete.";
        } else {
            return "This task has limited direct impact on your current priorities. Consider delegating it or moving it to a backlog for later review.";
        }
    }
}

