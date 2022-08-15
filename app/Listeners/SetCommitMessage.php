<?php

namespace App\Listeners;

use App\Events\CommitIDSentEvent;
use Illuminate\Support\Facades\Http;

class SetCommitMessage
{

    /**
     * Handle the event.
     *
     * @param CommitIDSentEvent $event
     * @return void
     */
    public function handle(CommitIDSentEvent $event)
    {
        if (empty($commitId = $event->task->commit_id) || empty($githubRepo = $event->task->team->git_repo))
            return;

        $commitMessage = $this->getCommitMessage($commitId, $githubRepo);
        if (!empty($commitMessage))
            $event->task->taskMetas()->create([
                'task_key' => 'commit_message',
                'task_value' => $commitMessage,
            ]);
    }

    protected function getCommitMessage($commitId, $GithubRepo)
    {
        try {
            $githubRepoName = explode('/', $GithubRepo);
            $githubRepoName = array_pop($githubRepoName);
            return Http::navaxGithub()->get('/' . $githubRepoName . '/commits/' . $commitId)
                ->throw()->json()['commit']['message'];
        } catch (\Exception $e) {
            return null;
        }
    }
}
