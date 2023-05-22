<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

// Models
use App\Models\Event;

class EventNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This laravel cronjob is used to send Event Notification in time or before 15 mint.';

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
    public function handle()
    {
       Event::notificationAlert();
       echo "Done!";
    }
}
