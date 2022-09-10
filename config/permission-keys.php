<?php

return [
    'task' => [
        'ساخت تسک در (تیم،پروژه،کمپانی)' => 'can_create_task_in',
        'ویرایش تسک در (تیم،پروژه،کمپانی)' => 'can_update_task_in',
        'حذف تسک در (تیم،پروژه،کمپانی)' => 'can_delete_task_in',
    ],
    'team' => [
        'ساخت تیم در (پروژه،کمپانی)' => 'can_create_team_in',
        'ویرایش تیم در (پروژه،کمپانی)' => 'can_update_team_in',
        'حذف تیم در (پروژه،کمپانی)' => 'can_delete_team_in',
    ],
    'project' => [
        'ساخت پروژه در (کمپانی)' => 'can_create_project_in',
        'ویرایش پروژه در (کمپانی)' => 'can_update_project_in',
        'حذف پروژه در (کمپانی)' => 'can_delete_project_in',
    ],
    'company' => [
        'ساخت کمپانی' => 'can_create_company',
        'ویرایش کمپانی' => 'can_update_company_in',
        'حذف کمپانی' => 'can_delete_company_in',
    ],
    'others' => [
        'تغییر عضو (افزودن و کاستن اعضا)' => 'can_change_member_in',
        'تغییر واچر (افزودن و کاستن واچر)' => 'can_change_watcher_in',
        'دریافت واچر ها' => 'can_get_watchers_in' ,
        'دریافت اعضا' => 'can_get_members_in' ,
    ]
];
