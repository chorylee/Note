<?php

use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Tag::flushEventListeners();
        factory(\App\Models\Tag::class,60)->create();
    }
}
