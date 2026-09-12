<?php

function profile_index()
{
    require_auth();

    $user = user_find_by_id(current_user_id());
    if (!$user) {
        $_SESSION = array();
        session_destroy();
        redirect("/login");
    }

    if (!is_post()) {
        view("profile/index", array("title" => "Profile", "user" => $user, "errors" => array()));
        return;
    }

    verify_csrf();

    $data = array(
        "name" => trim($_POST["name"] ?? ""),
        "email" => strtolower(trim($_POST["email"] ?? "")),
        "address" => trim($_POST["address"] ?? ""),
        "phone" => trim($_POST["phone"] ?? ""),
        "current_password" => $_POST["current_password"] ?? "",
        "new_password" => $_POST["new_password"] ?? "",
        "profile_picture" => null
    );

    $errors = validate_profile($data, $user);
    list($upload_path, $upload_error) = upload_file("profile_picture", "profiles");

    if ($upload_error) {
        $errors["profile_picture"] = $upload_error;
    } else {
        $data["profile_picture"] = $upload_path;
    }

    if (count($errors) > 0) {
        if ($upload_path) {
            delete_public_file($upload_path);
            $data["profile_picture"] = $user["profile_picture"] ?? null;
        }

        $old_user = array_merge($user, $data);
        view("profile/index", array("title" => "Profile", "user" => $old_user, "errors" => $errors));
        return;
    }

    user_update_profile($user["id"], $data);

    if ($data["new_password"] != "") {
        user_update_password($user["id"], $data["new_password"]);
        remember_delete_for_user($user["id"]);
    }

    if ($upload_path) {
        delete_public_file($user["profile_picture"] ?? null);
    }

    $_SESSION["name"] = $data["name"];
    set_flash("success", "Profile updated.");
    redirect("/profile");
}

function validate_profile($data, $user)
{
    $errors = array();

    if ($data["name"] == "" || strlen($data["name"]) > 100) {
        $errors["name"] = "Name is required and must be under 100 characters.";
    }

    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email.";
    } elseif (user_email_exists($data["email"], $user["id"])) {
        $errors["email"] = "That email is already used by another account.";
    }

    if ($data["address"] == "") {
        $errors["address"] = "Address is required.";
    }

    if ($data["phone"] == "" || !preg_match("/^[0-9+\-\s]{6,30}$/", $data["phone"])) {
        $errors["phone"] = "Enter a valid phone number.";
    }

    if ($data["new_password"] != "") {
        if (strlen($data["new_password"]) < 8) {
            $errors["new_password"] = "New password must be at least 8 characters.";
        }

        if ($data["current_password"] == "" || !password_verify($data["current_password"], $user["password_hash"])) {
            $errors["current_password"] = "Current password is required to change password.";
        }
    }

    return $errors;
}
