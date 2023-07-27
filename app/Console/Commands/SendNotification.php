<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Notifications\TemplateDrivenNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification '
        .'{template : Template code defined in email_templates table} '
        .'{user? : Notification recipient email address. Optional if all option is passed) } '
        .'{--a|all : Send to all users (overrides user argument)} '
        .'{--r|resend : Send the given notification even if it was already sent}'
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $template_code = $this->argument('template');
        $template = EmailTemplate::findByCode($template_code);
        if(is_null($template)) {
            $this->error("Given template code '{$template_code}' could not be found!");
            return self::FAILURE;
        }
        /** @var string|null $user_email */
        $user_email = $this->argument('user');
        /** @var bool $send_to_all */
        $send_to_all = $this->option('all') ?? false;
        /** @var bool $resend */
        $resend = $this->option('resend');

        if($send_to_all) {
            return $this->send_to_all($template, $resend);
        }
        if(!is_null($user_email)) {
            return $this->send_to_user_by_email($template, $user_email, $resend);
        }

        $this->error("Either specify a user email or pass the --all (-a) option to send to all users");
        return self::FAILURE;
    }

    private function send_to_all(EmailTemplate $template, bool $resend): int {
        $this->warn("Option --all passed. Overriding user input if present.");
        $this->info("------------------------------------------------------");
        // chunking here might be a very good idea...
        $progress_bar = $this->output->createProgressBar(User::count());
        foreach (User::all() as $user) {
            $result = $this->send_to_user($template, $user, $resend);
            if ($result === self::FAILURE) {
                $this->warn("Notification {$template->code} was NOT sent to {$user->email}");
            }
            $progress_bar->advance();
        }
        $progress_bar->finish();
        $this->info("------------------------------------------------------");
        $this->info("Task completed! Notification {$template->code} sent to all users");
        return self::SUCCESS;
    }

    private function send_to_user_by_email(EmailTemplate $template, ?string $user_email, bool $resend): int {
        /** @var User $user */
        $user = User::findByEmail($user_email);
        if(is_null($user)) {
            $this->error("User {$user_email} not found");
            return self::FAILURE;
        }
        $this->info("Sending notification {$template->code} to user {$user_email}");
        return $this->send_to_user($template, $user, $resend);
    }

    private function send_to_user(EmailTemplate $template, User $user, bool $resend): int {
        try {
            // don't resend? Then check if user has notification
            // if yes, return FAILURE after warning
            if (!$resend && $this->user_has_notification($user, $template)) {
                $this->warn("User {$user->email} has already been notified with notification {$template->code}. "
                    . "To resend the notification pass the option --resend (-r).<");
                return self::FAILURE;
            }
            $user->notify(new TemplateDrivenNotification($template, $user));
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Exception! {$e->getMessage()}");
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function user_has_notification(User $user, EmailTemplate $template): bool {
        $like_value = sprintf('"template_code":"%s"', $template->code);
        return $user->notifications()
            ->where('type', 'App\\Notifications\\TemplateDrivenNotification')
            ->where('data', 'like', "%" . $like_value . "%")
            ->count() > 0;
    }

}
