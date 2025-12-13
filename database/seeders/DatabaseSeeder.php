<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $teacher = User::factory()->create([
            'name' => 'Heihachi Mishima',
            'email' => 'gabotamayo41@gmail.com',
            'password' => bcrypt('191423angpogiko'),
        ]);
        $teacher->assignRole(RoleName::TEACHER->value);

        $student = User::factory()->create([
            'name' => 'Kazuya Mishima',
            'email' => 'gabotamayo@yahoo.com',
            'password' => bcrypt('191423angpogiko'),
        ]);
        $student->assignRole(RoleName::STUDENT->value);
    }
}
