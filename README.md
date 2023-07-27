# POC - Laravel 8 custom template notifications

This project is intended as a small proof of concept (POC) to show the possibility to have custom mail notification templates to send to users.

In order to send a given notification an email_templates record is required. The record consists of a unique code, the html markup and a subject.

To send the notification email to a user run the command

```shell
php artisan send:notification [options] [--] <template> [<user>]
```

The template argument is the template code.

The "semi-optional" user argument is the email address of the user that will receive the notification. Semi-optional in the sense that it's optional if you pass the --all option.

The options available are

| option   | abbreviation | description                                             |
|----------|--------------|---------------------------------------------------------|
| --all    | -a           | Send to all users (overrides user argument)             |
| --resend | -r           | Send the given notification even if it was already sent |

Together with the artisan command options that are present by default.

**The command does not send the same notification (defined by the unique template code) twice to the same user unless the --resend option is passed.**

## POC components

This small application is composed of
- [EmailTemplate](app%2FModels%2FEmailTemplate.php) model and its [migration](database%2Fmigrations%2F2023_07_27_110229_create_email_templates_table.php) file
- Notifiable User model and its migration (in Laravel by default)
- [SendNotification.php](app%2FConsole%2FCommands%2FSendNotification.php) command
- [TemplateDrivenNotification](app%2FNotifications%2FTemplateDrivenNotification.php) class created with the make:notification artisan command
  - It uses the view [emails/generic_template.blade.php](resources%2Fviews%2Femails%2Fgeneric_template.blade.php) to render the template markup (or an empty string)
- [Migration](database%2Fmigrations%2F2023_07_27_105158_create_notifications_table.php) to create the notifications table obtained by running the notifications:table artisan command
- [UsersTableSeeder](database%2Fseeders%2FUsersTableSeeder.php) and [EmailTemplateSeeder](database%2Fseeders%2FEmailTemplateSeeder.php) to write some records

## Next steps

At the moment, the only way to create and modify mail templates is to manually write records into the database.

But nobody wants to do that, so I'm thinking about adding a UI to simplify the CRUD operations on the email_templates table, maybe with a [WYSIWYG editor](https://en.wikipedia.org/wiki/WYSIWYG).
I'm not sure if I'll add it to this project or I'll do it using a frontend framework (or a metaframework).

## Conclusion

This POC solves two problems (that I recently faced... together, unfortunately):
1) having a command that sends notifications to a bunch of email addresses failing mid-execution because of some error during the authentication phase to the SMTP server. The command had to be rerun, but I couldn't rerun it as it was since some emails were already been sent. I had to do some creative things to solve the issue in that instance...
2) having to create a new notification class (with all the necessary fuss) and redeploy the application for every notification that the client wants to send to the application users. Now, granted, this is not a big problem when you have to send something like 2 or 3 custom notifications per year during well expected time periods. For example, if you want to email your users to wish them happy holidays or notify them of a planned maintenance of the application during a certain time frame it's all good. The problem spawns when you are informed that your client wants to send a notification to the users by the end of the day and you and the only other team member who also has knowledge on the matter are both on vacation...

Now...
- Could this be done in a "cleaner" way? I guess so.
- Could we have been using an external service to handle such notifications to users? Surely.
- Could we use a headless CMS to handle the mail templates? Yes.
- Do I care? Not that much...

At the end of the day, this POC is an exercise and the destination it's not what matters. It never is. What truly matters is the journey... and the friends we made along the way (?)
