<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user_seeder = new UsersTableSeeder();
        $mail_template_seeder = new EmailTemplateSeeder();

        $user_seeder->run();
        $mail_template_seeder->run();
    }
}
