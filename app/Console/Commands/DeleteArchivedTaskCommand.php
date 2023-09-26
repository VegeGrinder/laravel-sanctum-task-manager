<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class DeleteArchivedTaskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-archived-task-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete archived Tasks older than 1 week.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Task::where('archived_date', '<=', now()->subDays(7))->count();
        Task::where('archived_date', '<=', now()->subDays(7))->delete();

        $this->info("Command finished, $count Task(s) deleted.");
    }
}
