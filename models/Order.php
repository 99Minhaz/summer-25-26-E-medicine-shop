<?php

function order_create_from_cart($user_id, $address, $payment_method)
{
    $items = cart_items($user_id);

    if (count($items) == 0) {
        return array("ok" => false, "message" => "Your cart is empty.");
    }

    mysqli_begin_transaction(db_connection_only());

    $total = 0;

    foreach ($items as $item) {
        if ((int) $item["quantity"] < 1 || (int) $item["quantity"] > (int) $item["availability"]) {
            mysqli_rollback(db_connection_only());
            return array("ok" => false, "message" => $item["name"] . " does not have enough stock.");
        }
        $total = $total + (float) $item["subtotal"];
    }

    db_run(
        "INSERT INTO orders (user_id, total_amount, shipping_address, status, payment_method)
         VALUES (?, ?, ?, 'pending', ?)",
        "idss",
        array($user_id, $total, $address, $payment_method)
    );

    $order_id = mysqli_insert_id(db_connection_only());

    foreach ($items as $item) {
        db_run(
            "INSERT INTO order_items (order_id, medicine_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)",
            "iiid",
            array($order_id, $item["medicine_id"], $item["quantity"], $item["price"])
        );

        db_run(
            "UPDATE medicines SET availability = availability - ? WHERE id = ? AND availability >= ?",
            "iii",
            array($item["quantity"], $item["medicine_id"], $item["quantity"])
        );

        if (mysqli_affected_rows(db_connection_only()) == 0) {
            mysqli_rollback(db_connection_only());
            return array("ok" => false, "message" => $item["name"] . " went out of stock.");
        }
    }

    $transaction_id = strtoupper(bin2hex(random_bytes(6)));
    db_run(
        "INSERT INTO payments (order_id, amount, payment_method, transaction_id)
         VALUES (?, ?, ?, ?)",
        "idss",
        array($order_id, $total, $payment_method, $transaction_id)
    );

    cart_clear($user_id);
    mysqli_commit(db_connection_only());

    return array("ok" => true, "order_id" => $order_id);
}

function order_find_for_user($order_id, $user_id)
{
    return db_one(
        "SELECT orders.*, payments.transaction_id
         FROM orders
         LEFT JOIN payments ON payments.order_id = orders.id
         WHERE orders.id = ? AND orders.user_id = ?
         LIMIT 1",
        "ii",
        array($order_id, $user_id)
    );
}

function order_items($order_id)
{
    return db_select(
        "SELECT order_items.*, medicines.name, medicines.vendor_name
         FROM order_items
         JOIN medicines ON medicines.id = order_items.medicine_id
         WHERE order_items.order_id = ?
         ORDER BY order_items.id",
        "i",
        array($order_id)
    );
}

function order_all()
{
    return db_select(
        "SELECT orders.*, users.name AS customer_name, users.email AS customer_email,
                delivery_user.name AS delivery_person_name
         FROM orders
         JOIN users ON users.id = orders.user_id
         LEFT JOIN users AS delivery_user ON delivery_user.id = orders.delivery_person_id
         ORDER BY orders.order_date DESC"
    );
}

function order_all_for_delivery($delivery_id)
{
    return db_select(
        "SELECT orders.*, users.name AS customer_name, users.phone AS customer_phone
         FROM orders
         JOIN users ON users.id = orders.user_id
         WHERE orders.delivery_person_id = ?
         ORDER BY FIELD(orders.status, 'accepted', 'delivered', 'rejected', 'pending'), orders.order_date DESC",
        "i",
        array($delivery_id)
    );
}

function order_assign_delivery($order_id, $delivery_id)
{
    $order = db_one("SELECT status FROM orders WHERE id = ? LIMIT 1", "i", array($order_id));

    if (!$order || $order["status"] != "accepted") {
        return false;
    }

    db_run("UPDATE orders SET delivery_person_id = ? WHERE id = ?", "ii", array($delivery_id, $order_id));
    return true;
}

function order_mark_delivered($order_id, $delivery_id)
{
    $order = db_one(
        "SELECT status, delivery_person_id FROM orders WHERE id = ? LIMIT 1",
        "i",
        array($order_id)
    );

    if (!$order || $order["status"] != "accepted" || (int) $order["delivery_person_id"] !== (int) $delivery_id) {
        return false;
    }

    db_run("UPDATE orders SET status = 'delivered' WHERE id = ?", "i", array($order_id));
    return true;
}

function order_accepted_history()
{
    return db_select(
        "SELECT orders.*, users.name AS customer_name, users.email AS customer_email, users.phone,
                GROUP_CONCAT(CONCAT(medicines.name, ' x ', order_items.quantity) ORDER BY medicines.name SEPARATOR ', ') AS medicines
         FROM orders
         JOIN users ON users.id = orders.user_id
         JOIN order_items ON order_items.order_id = orders.id
         JOIN medicines ON medicines.id = order_items.medicine_id
         WHERE orders.status = 'accepted'
         GROUP BY orders.id, orders.user_id, orders.total_amount, orders.shipping_address,
                  orders.status, orders.payment_method, orders.order_date,
                  users.name, users.email, users.phone
         ORDER BY orders.order_date DESC"
    );
}

function order_count_pending()
{
    $row = db_one("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'");
    return (int) $row["total"];
}

function order_update_status($order_id, $status)
{
    if ($status != "accepted" && $status != "rejected") {
        return false;
    }

    $order = db_one("SELECT status FROM orders WHERE id = ? LIMIT 1", "i", array($order_id));
    if (!$order) {
        return false;
    }

    if ($order["status"] == $status) {
        return true;
    }

    mysqli_begin_transaction(db_connection_only());

    if ($status == "rejected" && $order["status"] != "rejected") {
        order_restore_stock($order_id);
    }

    if ($status == "accepted" && $order["status"] == "rejected") {
        $stock_ok = order_reserve_stock_again($order_id);

        if (!$stock_ok) {
            mysqli_rollback(db_connection_only());
            return false;
        }
    }

    db_run("UPDATE orders SET status = ? WHERE id = ?", "si", array($status, $order_id));
    mysqli_commit(db_connection_only());
    return true;
}

function order_restore_stock($order_id)
{
    db_run(
        "UPDATE medicines
         JOIN order_items ON order_items.medicine_id = medicines.id
         SET medicines.availability = medicines.availability + order_items.quantity
         WHERE order_items.order_id = ?",
        "i",
        array($order_id)
    );
}

function order_reserve_stock_again($order_id)
{
    $items = order_items($order_id);

    foreach ($items as $item) {
        db_run(
            "UPDATE medicines SET availability = availability - ? WHERE id = ? AND availability >= ?",
            "iii",
            array($item["quantity"], $item["medicine_id"], $item["quantity"])
        );

        if (mysqli_affected_rows(db_connection_only()) == 0) {
            return false;
        }
    }

    return true;
}
