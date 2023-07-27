<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $template1 = new EmailTemplate();
        $template1->code = 'template.first';
        $template1->subject = 'Subject One';
        $template1->markup = '<div>Hello mail 1</div>';

        $template2 = new EmailTemplate();
        $template2->code = 'template.second';
        $template2->subject = 'Subject Two';
        $template2->markup = '<div>Hello mail 2</div>';
        $template2->markup .= '<div>This is another div</div>';

        $template1->save();
        $template2->save();
    }
}
