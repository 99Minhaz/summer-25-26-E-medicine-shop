<?php

function base_path()
{
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $dir = rtrim(str_replace("\\", "/", dirname($script)), "/");

    if ($dir == "/" || $dir == ".") {
        return "";
    }

    $parts = explode("/", $dir);
    $encoded_parts = array();

    foreach ($parts as $part) {
        $encoded_parts[] = rawurlencode(rawurldecode($part));
    }

    return implode("/", $encoded_parts);
}

function url($path = "")
{
    $path = ltrim($path, "/");
    if ($path == "") {
        return base_path() . "/";
    }
    return base_path() . "/" . $path;
}

function asset($path)
{
    return url(ltrim($path, "/"));
}

function redirect($path)
{
    header("Location: " . url($path));
    exit;
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function is_post()
{
    return ($_SERVER["REQUEST_METHOD"] ?? "GET") == "POST";
}

function csrf_token()
{
    if (!isset($_SESSION["_csrf"])) {
        $_SESSION["_csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["_csrf"];
}

function csrf_field()
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $token = $_POST["_csrf"] ?? ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "");

    if (!isset($_SESSION["_csrf"]) || !hash_equals($_SESSION["_csrf"], $token)) {
        if (wants_json()) {
            json_response(array("ok" => false, "message" => "Invalid security token. Refresh and try again."), 419);
        }

        set_flash("error", "Invalid security token. Refresh and try again.");
        redirect("/");
    }
}

function wants_json()
{
    $accept = $_SERVER["HTTP_ACCEPT"] ?? "";
    $requested = strtolower($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "");

    return strpos($accept, "application/json") !== false || $requested == "xmlhttprequest";
}

function json_response($data, $status = 200)
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data);
    exit;
}

function startup_error($title, $message)
{
    http_response_code(500);

    if (wants_json()) {
        json_response(array("ok" => false, "message" => $message), 500);
    }

    include BASE_PATH . "/views/errors/setup.php";
    exit;
}

function set_flash($key, $message)
{
    $_SESSION["_flash"][$key] = $message;
}

function flash($key)
{
    if (isset($_SESSION["_flash"][$key])) {
        $message = $_SESSION["_flash"][$key];
        unset($_SESSION["_flash"][$key]);
        return $message;
    }
    return null;
}

function current_user_id()
{
    if (isset($_SESSION["user_id"])) {
        return (int) $_SESSION["user_id"];
    }
    return null;
}

function current_role()
{
    return $_SESSION["role"] ?? null;
}

function current_user_name()
{
    return $_SESSION["name"] ?? null;
}

function require_auth()
{
    if (!current_user_id()) {
        set_flash("error", "Please login first.");
        redirect("/login");
    }
}

function require_role($role)
{
    require_auth();

    if (current_role() != $role) {
        set_flash("error", "You are not allowed to access that page.");
        redirect("/");
    }
}

function view($template, $data = array(), $layout = "layout/main")
{
    extract($data);
    ob_start();
    include BASE_PATH . "/views/" . $template . ".php";
    $content = ob_get_clean();
    include BASE_PATH . "/views/" . $layout . ".php";
}

function field_value($source, $key, $default = "")
{
    if (isset($source[$key])) {
        return e($source[$key]);
    }
    return e($default);
}

function upload_file($field, $folder)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]["error"] == UPLOAD_ERR_NO_FILE) {
        return array(null, null);
    }

    $file = $_FILES[$field];

    if ($file["error"] != UPLOAD_ERR_OK) {
        return array(null, "Upload failed.");
    }

    if ($file["size"] > UPLOAD_MAX_BYTES) {
        return array(null, "Image must be 2MB or smaller.");
    }

    $allowed = array("image/jpeg" => "jpg", "image/png" => "png");
    $mime = mime_content_type($file["tmp_name"]);

    if (!isset($allowed[$mime])) {
        return array(null, "Only JPEG and PNG images are allowed.");
    }

    $file_name = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
    $relative_path = "uploads/" . trim($folder, "/") . "/" . $file_name;
    $target_folder = BASE_PATH . "/uploads/" . trim($folder, "/");

    if (!is_dir($target_folder)) {
        mkdir($target_folder, 0755, true);
    }

    if (!move_uploaded_file($file["tmp_name"], $target_folder . "/" . $file_name)) {
        return array(null, "Could not save uploaded file.");
    }

    return array($relative_path, null);
}

function delete_public_file($relative_path)
{
    if (!$relative_path) {
        return;
    }

    $file = realpath(BASE_PATH . "/" . ltrim($relative_path, "/"));
    $uploads_root = realpath(BASE_PATH . "/uploads");

    if ($file && $uploads_root && strpos($file, $uploads_root) === 0 && is_file($file)) {
        unlink($file);
    }
}
