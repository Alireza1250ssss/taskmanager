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
        if (empty($commitId = $event->task->commit_id) || empty($githubRepo = $event->task->team->git_repo)
            || empty($accessToken = $event->task->team->github_access_token))
            return;

        $commitMessage = $this->getCommitMessage($commitId, $githubRepo,$accessToken);
        if (!empty($commitMessage))
            $event->task->taskMetas()->create([
                'task_key' => 'commit_message',
                'task_value' => $commitMessage,
            ]);
    }

    protected function getCommitMessage($commitId, $GithubRepo,string $accessToken)
    {
        try {
            $githubAddressInfo = explode('/', $GithubRepo);
            $githubRepoName = array_pop($githubAddressInfo);
            $githubUserName = array_pop($githubAddressInfo);

            return Http::github($githubUserName,$githubRepoName,$accessToken)->get('/commits/' . $commitId)
                ->throw()->json()['commit']['message'];
        } catch (\Exception $e) {
            return null;
        }
    }
}
