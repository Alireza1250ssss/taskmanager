<?php

namespace App\Providers;

use App\Events\CommitIDSentEvent;
use App\Events\ModelRetrievedEvent;
use App\Events\PermissionAdded;
use App\Listeners\CheckRetrieveModel;
use App\Listeners\SetCommitMessage;
use App\Listeners\SetParentsReadPermission;
use App\Models\Company;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Observers\BaseObserver;
use App\Observers\CompanyObserver;
use App\Observers\SetLeaveScheduleObserver;
use App\Observers\TaskObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PermissionAdded::class => [
          SetParentsReadPermission::class
        ],
        CommitIDSentEvent::class => [
          SetCommitMessage::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {


//        Leave::observe([SetLeaveScheduleObserver::class,BaseObserver::class]);
        Company::observe([BaseObserver::class]);
        Project::observe([BaseObserver::class]);
        Team::observe([BaseObserver::class]);
        Task::observe([BaseObserver::class]);
//        User::observe([BaseObserver::class]); !!!!!!!!!!!!!!!!!!!!! DO NOT UNCOMMENT THIS !!!!!!!!!!!!!

    }
}
