<?php

namespace App\Listeners;

use App\Events\UserLogEvent;
use App\Models\UserLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserLogListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserLogEvent  $event
     * @return void
     */
    public function handle(UserLogEvent $event)
    {
        UserLog::record($event->action, $event->refer_id, $event->refer_type);
    }
}
