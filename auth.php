<?php
require_once 'config.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_member() {
    return is_logged_in() && $_SESSION['user_type'] == USER_MEMBER;
}

function is_trainer() {
    return is_logged_in() && $_SESSION['user_type'] == USER_TRAINER;
}

function is_admin() {
    return is_logged_in() && $_SESSION['user_type'] == USER_ADMIN;
}

function require_login() {
    if(!is_logged_in()) {
        redirect('login.php');
    }
}

function require_member() {
    require_login();
    if(!is_member()) {
        redirect('dashboard.php');
    }
}

function require_trainer() {
    require_login();
    if(!is_trainer()) {
        redirect('dashboard.php');
    }
}

function require_admin() {
    require_login();
    if(!is_admin()) {
        redirect('dashboard.php');
    }
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_current_user_type() {
    return $_SESSION['user_type'] ?? null;
}
?>