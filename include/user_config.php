<?php
// Logged in user configuration


// Team Lead Details
/*
define('CURRENT_USER', [
    'staffid' => 'CSLS001',
    'email' => 'mike.j@acn.aero',
    'position' => 'TeamLead',
    'deptunitcode' => 'SLS',
    'subdeptunitcode' => 'CSLS',
    'isAdmin' => false
]);*/

// Dept Unit Lead Details

define('CURRENT_USER', [
    'staffid' => 'SLS001',
    'email' => 'samuel.a@acn.aero',
    'position' => 'DeptUnitLead',
    'deptunitcode' => 'SLS',
    'subdeptunitcode' => '',
    'isAdmin' => false
]);
// Head of Department Details
/*
define('CURRENT_USER', [
    'staffid' => 'COM001',
    'email' => 'jane.s@acn.aero',
    'position' => 'HOD',
    'departmentcode' => 'COM',
    'deptunitcode' => '',
    'subdeptunitcode' => '',
    'isAdmin' => false
]);
*/


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
