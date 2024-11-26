<?php
// Current user configuration
// define('CURRENT_USER', [
//     'staffid' => 'DMKT001',  // Mike Johnson - Digital Marketing Team Lead
//     'email' => 'mike.j@acn.aero',
//     'position' => 'TeamLead',
//     'deptunitcode' => 'MKT',
//     'subdeptunitcode' => 'DMKT',
//     'isAdmin' => false
// ]);

// Current user configuration
define('CURRENT_USER', [
    'staffid' => 'SLS001',
    'email' => 'samuel.a@acn.aero',
    'position' => 'DeptUnitLead',
    'deptunitcode' => 'SLS',
    'subdeptunitcode' => '',
    'isAdmin' => false
]);

// Function to get current user info
function getCurrentUser($key = null)
{
    if ($key) {
        return CURRENT_USER[$key] ?? null;
    }
    return CURRENT_USER;
}

// Initialize session with hardcoded values
function initializeUserSession()
{
    $_SESSION['staffid'] = CURRENT_USER['staffid'];
    $_SESSION['email'] = CURRENT_USER['email'];
    $_SESSION['position'] = CURRENT_USER['position'];
    $_SESSION['deptunitcode'] = CURRENT_USER['deptunitcode'];
    $_SESSION['subdeptunitcode'] = CURRENT_USER['subdeptunitcode'];
    $_SESSION['isAdmin'] = CURRENT_USER['isAdmin'];
}
