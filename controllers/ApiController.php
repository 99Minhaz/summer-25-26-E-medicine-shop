<?php

function api_search_medicines()
{
    $filters = array(
        "q" => trim($_GET["q"] ?? ""),
        "vendor" => trim($_GET["vendor"] ?? ""),
        "genre" => trim($_GET["genre"] ?? ""),
        "type" => trim($_GET["type"] ?? "")
    );

    $medicines = medicine_all($filters);

    ob_start();
    foreach ($medicines as $medicine) {
        include BASE_PATH . "/views/home/_medicine_card.php";
    }
    $html = ob_get_clean();

    json_response(array(
        "ok" => true,
        "count" => count($medicines),
        "html" => $html,
        "medicines" => $medicines
    ));
}

function api_cart_add()
{
    require_customer_json();
    verify_csrf();

    $medicine_id = (int) ($_POST["medicine_id"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? 0);
    $medicine = medicine_find($medicine_id);

    if (!$medicine) {
        json_response(array("ok" => false, "message" => "Medicine not found."), 404);
    }

    if ($quantity < 1) {
        json_response(array("ok" => false, "message" => "Quantity must be a positive number."), 422);
    }

    $current_quantity = cart_quantity_for(current_user_id(), $medicine_id);
    if (($current_quantity + $quantity) > (int) $medicine["availability"]) {
        json_response(array("ok" => false, "message" => "Requested quantity exceeds available stock."), 422);
    }

    cart_add(current_user_id(), $medicine_id, $quantity);

    json_response(array(
        "ok" => true,
        "message" => "Added to cart.",
        "cartCount" => cart_count(current_user_id())
    ));
}

function api_cart_update()
{
    require_customer_json();
    verify_csrf();

    $medicine_id = (int) ($_POST["medicine_id"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? 0);
    $medicine = medicine_find($medicine_id);

    if (!$medicine) {
        json_response(array("ok" => false, "message" => "Medicine not found."), 404);
    }

    if ($quantity < 1 || $quantity > (int) $medicine["availability"]) {
        json_response(array("ok" => false, "message" => "Quantity must be between 1 and available stock."), 422);
    }

    cart_set_quantity(current_user_id(), $medicine_id, $quantity);
    json_response(cart_json_payload());
}

function api_cart_remove()
{
    require_customer_json();
    verify_csrf();

    $medicine_id = (int) ($_POST["medicine_id"] ?? 0);
    cart_remove(current_user_id(), $medicine_id);
    json_response(cart_json_payload());
}

function api_order_status()
{
    require_admin_json();
    verify_csrf();

    $order_id = (int) ($_POST["order_id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    $updated = order_update_status($order_id, $status);

    if (!$updated) {
        json_response(array("ok" => false, "message" => "Could not update order."), 422);
    }

    json_response(array("ok" => true, "message" => "Order status updated.", "status" => $status));
}

function cart_json_payload()
{
    $items = cart_items(current_user_id());
    $total = cart_total(current_user_id());

    ob_start();
    include BASE_PATH . "/views/cart/_items.php";
    $html = ob_get_clean();

    return array(
        "ok" => true,
        "html" => $html,
        "total" => number_format($total, 2),
        "cartCount" => cart_count(current_user_id())
    );
}

function require_customer_json()
{
    if (!current_user_id() || current_role() != "customer") {
        json_response(array("ok" => false, "message" => "Please login as a customer."), 401);
    }
}

function require_admin_json()
{
    if (!current_user_id() || current_role() != "admin") {
        json_response(array("ok" => false, "message" => "Admin access required."), 403);
    }
}
