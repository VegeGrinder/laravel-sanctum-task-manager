# 09202023-goh-shein-web

## Environment
npm (9.7.2)  
node (v20.4.0)  
composer (2.6.3)  
php (8.2.10)

## Installation
Run these commands
```
composer install
npm install

php artisan db:seed --class=DatabaseSeeder
```

## Login
Email: test@example.com  
Password: password

## Archived Task Deletion
In \App\Console\Kernel.php, the scheduler is running a command (DeleteArchivedTaskCommand) to fetch Tasks with 'archived_date' over 7 days and delete them daily
```
php artisan app:delete-archived-task-command
```
To simulate this, you may set a Task with 'archived_date' 7 days before your current datetime directly in your database, then run the above command

## Archived Task Deletion
To view the API documentation made by a documentation tool (Scramble - https://scramble.dedoc.co)
```
http://127.0.0.1:8000/docs/api (access Laravel Web URL with '/docs/api')
```
Note: All '{id}' in the API endpoint are protected by EnsureTaskBelongsToUser middleware, which may not be shown in the auto-generated API documentation, returning Response Code 404 if the Task does not exist, or Response Code 401 if the Task does not belong to the User
