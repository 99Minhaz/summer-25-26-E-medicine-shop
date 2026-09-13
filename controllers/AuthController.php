<?php

function attempt_remembered_login()
{
    if (current_user_id() || !isset($_COOKIE["remember_me"])) {
        return;
    }

    $parts = explode(":", $_COOKIE["remember_me"]);
    if (count($parts) != 2) {
        return;
    }

    $selector = $parts[0];
    $token = $parts[1];

    remember_delete_expired();
    $saved_token = remember_find_token($selector);

    if (!$saved_token || !password_verify($token, $saved_token["token_hash"])) {
        remember_delete_token($selector);
        clear_remember_cookie();
        return;
    }

    login_user($saved_token);
}

function auth_register()
{
    if (!is_post()) {
        view("auth/register", array("title" => "Register", "errors" => array(), "old" => array()));
        return;
    }

    verify_csrf();

    $data = array(
        "name" => trim($_POST["name"] ?? ""),
        "email" => strtolower(trim($_POST["email"] ?? "")),
        "password" => $_POST["password"] ?? "",
        "role" => $_POST["role"] ?? "customer",
        "address" => trim($_POST["address"] ?? ""),
        "phone" => trim($_POST["phone"] ?? "")
    );

    $errors = validate_registration($data);

    if (count($errors) > 0) {
        view("auth/register", array("title" => "Register", "errors" => $errors, "old" => $data));
        return;
    }

    user_create($data);
    set_flash("success", "Account created. Please login.");
    redirect("/login");
}

function auth_login()
{
    if (!is_post()) {
        view("auth/login", array("title" => "Login", "errors" => array(), "old" => array()));
        return;
    }

    verify_csrf();

    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $remember = isset($_POST["remember"]);
    $errors = array();

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email.";
    }

    if ($password == "") {
        $errors["password"] = "Password is required.";
    }

    $user = null;
    if (count($errors) == 0) {
        $user = user_find_by_email($email);
    }

    if (count($errors) == 0 && (!$user || !password_verify($password, $user["password_hash"]))) {
        $errors["login"] = "Email or password is incorrect.";
    }

    if (count($errors) > 0) {
        view("auth/login", array("title" => "Login", "errors" => $errors, "old" => array("email" => $email)));
        return;
    }

    login_user($user);

    if ($remember) {
        remember_user($user["id"]);
    }

    redirect("/");
}

function auth_logout()
{
    verify_csrf();

    if (isset($_COOKIE["remember_me"])) {
        $parts = explode(":", $_COOKIE["remember_me"]);
        if (isset($parts[0])) {
            remember_delete_token($parts[0]);
        }
        clear_remember_cookie();
    }

    $_SESSION = array();
    session_destroy();
    redirect("/login");
}

function login_user($user)
{
    session_regenerate_id(true);
    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["role"] = $user["role"];
}

function remember_user($user_id)
{
    $selector = bin2hex(random_bytes(16));
    $token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 60 * 60);
    $token_hash = password_hash($token, PASSWORD_DEFAULT);

    remember_store_token($user_id, $selector, $token_hash, date("Y-m-d H:i:s", $expires));

    setcookie("remember_me", $selector . ":" . $token, array(
        "expires" => $expires,
        "path" => "/",
        "httponly" => true,
        "samesite" => "Lax"
    ));
}

function clear_remember_cookie()
{
    setcookie("remember_me", "", array(
        "expires" => time() - 3600,
        "path" => "/",
        "httponly" => true,
        "samesite" => "Lax"
    ));
}

function validate_registration($data)
{
    $errors = array();

    if ($data["name"] == "" || strlen($data["name"]) > 100) {
        $errors["name"] = "Name is required and must be under 100 characters.";
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email.";
    } elseif (user_email_exists($data["email"])) {
        $errors["email"] = "This email is already registered.";
    }

    if (strlen($data["password"]) < 8) {
        $errors["password"] = "Password must be at least 8 characters.";
    }

    if ($data["role"] != "admin" && $data["role"] != "customer") {
        $errors["role"] = "Choose a valid role.";
    }

    if ($data["address"] == "") {
        $errors["address"] = "Address is required.";
    }

    if ($data["phone"] == "" || !preg_match("/^[0-9+\-\s]{6,30}$/", $data["phone"])) {
        $errors["phone"] = "Enter a valid phone number.";
    }

    return $errors;
}
