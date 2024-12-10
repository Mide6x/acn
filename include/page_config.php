<?php
define('BASE_URL', '/acnnew');

$PAGE_URLS = [
    'TEAM_LEAD' => [
        'view' => BASE_URL . '/TL/TeamLead.php',
        'create' => BASE_URL . '/TL/create.php'
    ],
    'DEPT_UNIT_LEAD' => [
        'view' => BASE_URL . '/DUL/DeptUnitLead.php',
        'create' => BASE_URL . '/DUL/create_request.php'
    ],
    'HOD' => [
        'view' => BASE_URL . '/HOD/HODView.php',
        'create' => BASE_URL . '/HOD/create_requesthod.php'
    ],
    'HR' => [
        'view' => BASE_URL . '/HR/HRview.php',
        'dashboard' => BASE_URL . '/HR/HRdash.php'
    ],
    'HEAD_OF_HR' => [
        'view' => BASE_URL . '/HeadOfHR/HHRView.php'
    ],
    'CFO' => [
        'view' => BASE_URL . '/CFO/CFOView.php'
    ],
    'CEO' => [
        'view' => BASE_URL . '/CEO/CEOView.php'
    ],
    'SETTINGS' => [
        'departments' => BASE_URL . '/departments.php',
        'stations' => BASE_URL . '/station.php',
        'job_titles' => BASE_URL . '/jobtitles.php'
    ]
];
