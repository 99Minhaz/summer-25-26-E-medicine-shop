<?php

function user_create($data)
{
    $password_hash = password_hash($data["password"], PASSWORD_DEFAULT);

    db_run(
        "INSERT INTO users (name, email, password_hash, role, address, phone) VALUES (?, ?, ?, ?, ?, ?)",
        "ssssss",
        array($data["name"], $data["email"], $password_hash, $data["role"], $data["address"], $data["phone"])
    );

    return mysqli_insert_id(db_connection_only());
}

function user_find_by_email($email)
{
    return db_one("SELECT * FROM users WHERE email = ? LIMIT 1", "s", array($email));
}

function user_find_by_id($id)
{
    return db_one("SELECT * FROM users WHERE id = ? LIMIT 1", "i", array($id));
}

function user_email_exists($email, $skip_id = 0)
{
    if ($skip_id > 0) {
        $user = db_one("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1", "si", array($email, $skip_id));
    } else {
        $user = db_one("SELECT id FROM users WHERE email = ? LIMIT 1", "s", array($email));
    }

    return $user ? true : false;
}

function user_update_profile($id, $data)
{
    if ($data["profile_picture"]) {
        db_run(
            "UPDATE users SET name = ?, email = ?, address = ?, phone = ?, profile_picture = ? WHERE id = ?",
            "sssssi",
            array($data["name"], $data["email"], $data["address"], $data["phone"], $data["profile_picture"], $id)
        );
    } else {
        db_run(
            "UPDATE users SET name = ?, email = ?, address = ?, phone = ? WHERE id = ?",
            "ssssi",
            array($data["name"], $data["email"], $data["address"], $data["phone"], $id)
        );
    }
}

function user_update_password($id, $password)
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    db_run("UPDATE users SET password_hash = ? WHERE id = ?", "si", array($password_hash, $id));
}

function user_all_customers()
{
    return db_select(
        "SELECT id, name, email, phone, address, created_at
         FROM users
         WHERE role = 'customer'
         ORDER BY created_at DESC"
    );
}

function user_all_by_role($role)
{
    return db_select(
        "SELECT id, name, email, phone FROM users WHERE role = ? ORDER BY name",
        "s",
        array($role)
    );
}

function user_delete_customer($id)
{
    db_run("DELETE FROM users WHERE id = ? AND role = 'customer'", "i", array($id));
    return mysqli_affected_rows(db_connection_only()) > 0;
}

function user_count_customers()
{
    $row = db_one("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
    return (int) $row["total"];
}

function remember_store_token($user_id, $selector, $token_hash, $expires_at)
{
    db_run(
        "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)",
        "isss",
        array($user_id, $selector, $token_hash, $expires_at)
    );
}

function remember_find_token($selector)
{
    return db_one(
        "SELECT remember_tokens.*, users.name, users.email, users.role
         FROM remember_tokens
         JOIN users ON users.id = remember_tokens.user_id
         WHERE remember_tokens.selector = ? AND remember_tokens.expires_at > NOW()
         LIMIT 1",
        "s",
        array($selector)
    );
}

function remember_delete_token($selector)
{
    db_run("DELETE FROM remember_tokens WHERE selector = ?", "s", array($selector));
}

function remember_delete_for_user($user_id)
{
    db_run("DELETE FROM remember_tokens WHERE user_id = ?", "i", array($user_id));
}

function remember_delete_expired()
{
    db_run("DELETE FROM remember_tokens WHERE expires_at <= NOW()");
}
