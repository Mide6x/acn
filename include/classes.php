<?php

if (file_exists('../class/Revenue.php')) {
    include_once '../class/Revenue.php';
} elseif (file_exists('class/Revenue.php')) {
    include_once 'class/Revenue.php';
}

$revenue = new Revenue($con);
