<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommentsSeeder extends Seeder
{
    public function run()
    {
        $comments = [
            [
                'article_id' => 1,
                'user_id' => 2,
                'content' => 'Super article !',
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'article_id' => 1,
                'user_id' => 3,
                'content' => 'Très intéressant, merci !',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'article_id' => 2,
                'user_id' => 1,
                'content' => 'Belle réflexion',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'article_id' => 3,
                'user_id' => 1,
                'content' => 'Je suis d\'accord',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'article_id' => 4,
                'user_id' => 3,
                'content' => 'Excellente analyse',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];

        DB::table('comments')->insert($comments);
    }
}
