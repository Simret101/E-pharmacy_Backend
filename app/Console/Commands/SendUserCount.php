<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Mail;
use App\Mail\SendUserCountMail;

class SendUserCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:user-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        info ("CRON JOB running...");
        $count=User::whereDate("created_at",today())->count();
        Mail::to("semretb74@gmail.com")->send(new SendUserCountMail($count));
    }
}
