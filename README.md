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
In \App\Console\Kernel.php, the scheduler is running a command to fetch Tasks with 'archived_date' over 7 days and delete them daily
```
php artisan app:delete-archived-task-command
```
To simulated this, you may set a Task with 'archived_date' 7 days before your current datetime, then run the above command
