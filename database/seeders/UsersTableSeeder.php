<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new User([
                'name' => 'User' . $i,
                'email' => 'user' . $i . '@email.com',
                'password' => bcrypt('password'),
            ]);
            $user->save();
        }
    }
}
