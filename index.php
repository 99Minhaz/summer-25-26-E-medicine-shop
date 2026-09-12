<?php

define("BASE_PATH", __DIR__);

include BASE_PATH . "/config/app.php";
include BASE_PATH . "/helpers/helpers.php";
include BASE_PATH . "/config/database.php";

include BASE_PATH . "/models/User.php";
include BASE_PATH . "/models/Category.php";
include BASE_PATH . "/models/Medicine.php";
include BASE_PATH . "/models/Cart.php";
include BASE_PATH . "/models/Order.php";

include BASE_PATH . "/controllers/AuthController.php";
include BASE_PATH . "/controllers/HomeController.php";
include BASE_PATH . "/controllers/ProfileController.php";
include BASE_PATH . "/controllers/AdminController.php";
include BASE_PATH . "/controllers/CartController.php";
include BASE_PATH . "/controllers/OrderController.php";
include BASE_PATH . "/controllers/ApiController.php";
include BASE_PATH . "/controllers/VendorController.php";
include BASE_PATH . "/controllers/DeliveryController.php";

attempt_remembered_login();
route_request();

function route_request()
{
    $route = get_current_route();
    $method = $_SERVER["REQUEST_METHOD"] ?? "GET";

    if ($route == "/" || $route == "/home") {
        home_index();
    } elseif (preg_match("#^/category/([0-9]+)$#", $route, $match)) {
        home_category($match[1]);
    } elseif ($route == "/register") {
        auth_register();
    } elseif ($route == "/login") {
        auth_login();
    } elseif ($route == "/logout" && $method == "POST") {
        auth_logout();
    } elseif ($route == "/profile") {
        profile_index();
    } elseif ($route == "/admin") {
        admin_dashboard();
    } elseif ($route == "/admin/categories") {
        admin_categories();
    } elseif (preg_match("#^/admin/categories/edit/([0-9]+)$#", $route, $match)) {
        admin_edit_category($match[1]);
    } elseif (preg_match("#^/admin/categories/delete/([0-9]+)$#", $route, $match) && $method == "POST") {
        admin_delete_category($match[1]);
    } elseif ($route == "/admin/medicines") {
        admin_medicines();
    } elseif ($route == "/admin/medicines/create") {
        admin_create_medicine();
    } elseif (preg_match("#^/admin/medicines/edit/([0-9]+)$#", $route, $match)) {
        admin_edit_medicine($match[1]);
    } elseif (preg_match("#^/admin/medicines/delete/([0-9]+)$#", $route, $match) && $method == "POST") {
        admin_delete_medicine($match[1]);
    } elseif ($route == "/admin/customers") {
        admin_customers();
    } elseif (preg_match("#^/admin/customers/delete/([0-9]+)$#", $route, $match) && $method == "POST") {
        admin_delete_customer($match[1]);
    } elseif ($route == "/admin/orders") {
        admin_orders();
    } elseif (preg_match("#^/admin/orders/assign/([0-9]+)$#", $route, $match) && $method == "POST") {
        admin_assign_delivery($match[1]);
    } elseif ($route == "/admin/history") {
        admin_history();
    } elseif ($route == "/vendor") {
        vendor_dashboard();
    } elseif ($route == "/vendor/medicines") {
        vendor_medicines();
    } elseif ($route == "/vendor/medicines/create") {
        vendor_create_medicine();
    } elseif (preg_match("#^/vendor/medicines/edit/([0-9]+)$#", $route, $match)) {
        vendor_edit_medicine($match[1]);
    } elseif (preg_match("#^/vendor/medicines/delete/([0-9]+)$#", $route, $match) && $method == "POST") {
        vendor_delete_medicine($match[1]);
    } elseif ($route == "/delivery") {
        delivery_dashboard();
    } elseif (preg_match("#^/delivery/orders/([0-9]+)/deliver$#", $route, $match) && $method == "POST") {
        delivery_mark_delivered($match[1]);
    } elseif ($route == "/cart") {
        cart_index();
    } elseif ($route == "/checkout") {
        checkout_page();
    } elseif ($route == "/payment") {
        payment_page();
    } elseif ($route == "/orders/success") {
        order_success();
    } elseif ($route == "/api/medicines/search") {
        api_search_medicines();
    } elseif ($route == "/api/cart/add" && $method == "POST") {
        api_cart_add();
    } elseif ($route == "/api/cart/update" && $method == "POST") {
        api_cart_update();
    } elseif ($route == "/api/cart/remove" && $method == "POST") {
        api_cart_remove();
    } elseif ($route == "/api/orders/status" && $method == "POST") {
        api_order_status();
    } else {
        http_response_code(404);
        view("errors/404", array("title" => "Page not found"));
    }
}

function get_current_route()
{
    $path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
    $path = rawurldecode($path);
    $base = rawurldecode(base_path());

    if ($base != "" && substr($path, 0, strlen($base)) == $base) {
        $path = substr($path, strlen($base));
    }

    if ($path == "" || $path == "/index.php") {
        return "/";
    }

    if (substr($path, 0, 11) == "/index.php/") {
        $path = substr($path, 10);
    }

    $path = "/" . trim($path, "/");
    if ($path == "/") {
        return "/";
    }

    return rtrim($path, "/");
}
