<?php


namespace App\Http\Traits;


use Illuminate\Auth\Access\AuthorizationException;

trait AllowedFieldTrait
{
    // boolean to check only allowed field (which are defined on the condition access is getting unlocked) is entered
    public bool $isOnlyAllowedFields = false;
    public array $allowedFields = [];
    public array $allowFieldBasedOnPermissions = [
        'can_create_task_in' => ['title', 'team_ref_id', 'card_type_ref_id'],
        'can_create_team_in' => ['name', 'project_ref_id'],
        'can_create_project_in' => ['name', 'company_ref_id'],
        'can_create_company_in' => ['name']
    ];

    /**
     * @throws AuthorizationException
     */
    protected function CheckOnlyForReject()
    {
        if (!$this->isOnlyAllowedFields)
            throw new AuthorizationException('فیلد هایی غیر از فیلد های مجاز وارد کرده اید');
    }

    protected function mergeAllowedFieldForPermission(string $keyPermission)
    {
        if (array_key_exists($keyPermission, $this->allowFieldBasedOnPermissions))
            $this->allowedFields = array_merge($this->allowedFields, $this->allowFieldBasedOnPermissions[$keyPermission]);
    }
}
