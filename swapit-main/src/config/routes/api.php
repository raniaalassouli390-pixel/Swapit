<?php

$request = $_SERVER['REQUEST_URI'];

if ($request == '/api/login') {
    echo "Login API";
}

if ($request == '/api/register') {
    echo "Register API";
}

?>
