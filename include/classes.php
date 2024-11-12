<?php

if (file_exists('../class/rev.php')) {
    include_once '../class/rev.php';
} elseif (file_exists('class/rev.php')) {
    include_once 'class/rev.php';
}

$revenue = new revenue($con);
