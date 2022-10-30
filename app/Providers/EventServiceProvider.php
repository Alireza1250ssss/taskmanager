<?php

namespace App\Providers;

use App\Events\CommitIDSentEvent;
use App\Events\ModelRetrievedEvent;
use App\Events\PermissionAdded;
use App\Listeners\CheckRetrieveModel;
use App\Listeners\SetCommitMessage;
use App\Listeners\SetParentsReadPermission;
use App\Models\Attachment;
use App\Models\CardType;
use App\Models\Column;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskMeta;
use App\Models\Team;
use App\Models\User;
use App\Observers\BaseObserver;
use App\Observers\CompanyObserver;
use App\Observers\DefaultCardForCompanyObserver;
use App\Observers\DeleteRelationObserver;
use App\Observers\MetaFireEventOnMainModelObserver;
use App\Observers\OwnerObserver;
use App\Observers\SetLeaveScheduleObserver;
use App\Observers\TaskLogObserver;
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
        Company::observe([
            BaseObserver::class,
            OwnerObserver::class,
            DefaultCardForCompanyObserver::class,
            DeleteRelationObserver::class
        ]);
        Project::observe([
            BaseObserver::class,
            OwnerObserver::class,
            DeleteRelationObserver::class
        ]);
        Team::observe([
            BaseObserver::class ,
            OwnerObserver::class,
            DeleteRelationObserver::class
        ]);
        Task::observe([
            TaskObserver::class,
            TaskLogObserver::class,
            DeleteRelationObserver::class
        ]);
        TaskMeta::observe([TaskLogObserver::class,MetaFireEventOnMainModelObserver::class]);
        Comment::observe([TaskLogObserver::class]);
        CardType::observe([DeleteRelationObserver::class]);
        Column::observe([DeleteRelationObserver::class]);
        Attachment::observe([DeleteRelationObserver::class]);
//        User::observe([BaseObserver::class]); !!!!!!!!!!!!!!!!!!!!! DO NOT UNCOMMENT THIS !!!!!!!!!!!!!

    }
}
