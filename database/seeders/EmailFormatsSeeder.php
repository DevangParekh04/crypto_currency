<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailFormatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $emailFormat = [
            'title' => 'Forget Password',
            'variables' => '%name%,%otp%',
            'emailformat' => '<p>Hello %name%,<br>
                <br>Your Crypto account email verification OTP is <strong>%otp%</strong>.
                <br><br>
                This OTP will expire in 2 minutes.<br><br>Thank You<br>Crypto Team.</p>',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        DB::table('email_formats')->insert($emailFormat);
    }
}
