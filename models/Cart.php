<?php

function cart_items($user_id)
{
    return db_select(
        "SELECT cart.id AS cart_id, cart.quantity, medicines.id AS medicine_id,
                medicines.name, medicines.vendor_name, medicines.price, medicines.availability,
                medicines.image_path, categories.name AS category_name,
                (cart.quantity * medicines.price) AS subtotal
         FROM cart
         JOIN medicines ON medicines.id = cart.medicine_id
         JOIN categories ON categories.id = medicines.category_id
         WHERE cart.user_id = ?
         ORDER BY cart.added_at DESC",
        "i",
        array($user_id)
    );
}

function cart_count($user_id)
{
    $row = db_one("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?", "i", array($user_id));
    return (int) $row["total"];
}

function cart_total($user_id)
{
    $row = db_one(
        "SELECT COALESCE(SUM(cart.quantity * medicines.price), 0) AS total
         FROM cart
         JOIN medicines ON medicines.id = cart.medicine_id
         WHERE cart.user_id = ?",
        "i",
        array($user_id)
    );
    return (float) $row["total"];
}

function cart_quantity_for($user_id, $medicine_id)
{
    $row = db_one(
        "SELECT quantity FROM cart WHERE user_id = ? AND medicine_id = ? LIMIT 1",
        "ii",
        array($user_id, $medicine_id)
    );

    if ($row) {
        return (int) $row["quantity"];
    }
    return 0;
}

function cart_add($user_id, $medicine_id, $quantity)
{
    db_run(
        "INSERT INTO cart (user_id, medicine_id, quantity)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), added_at = CURRENT_TIMESTAMP",
        "iii",
        array($user_id, $medicine_id, $quantity)
    );
}

function cart_set_quantity($user_id, $medicine_id, $quantity)
{
    db_run(
        "UPDATE cart SET quantity = ? WHERE user_id = ? AND medicine_id = ?",
        "iii",
        array($quantity, $user_id, $medicine_id)
    );
}

function cart_remove($user_id, $medicine_id)
{
    db_run("DELETE FROM cart WHERE user_id = ? AND medicine_id = ?", "ii", array($user_id, $medicine_id));
}

function cart_clear($user_id)
{
    db_run("DELETE FROM cart WHERE user_id = ?", "i", array($user_id));
}
