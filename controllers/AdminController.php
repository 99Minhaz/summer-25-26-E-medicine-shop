<?php

function admin_dashboard()
{
    require_role("admin");

    $stats = db_call_one("CALL sp_dashboard_stats()");

    view("admin/dashboard", array(
        "title" => "Admin Dashboard",
        "counts" => array(
            "medicines" => (int) ($stats["medicines_count"] ?? 0),
            "categories" => (int) ($stats["categories_count"] ?? 0),
            "customers" => (int) ($stats["customers_count"] ?? 0),
            "pending_orders" => (int) ($stats["pending_orders_count"] ?? 0)
        )
    ));
}

function admin_categories()
{
    require_role("admin");

    $errors = array();
    $old = array();

    if (is_post()) {
        verify_csrf();
        $old = category_form_data();
        $errors = validate_category($old);

        if (count($errors) == 0) {
            category_create($old);
            set_flash("success", "Category created.");
            redirect("/admin/categories");
        }
    }

    view("admin/categories", array(
        "title" => "Categories",
        "categories" => category_all(),
        "errors" => $errors,
        "old" => $old
    ));
}

function admin_edit_category($id)
{
    require_role("admin");

    $category = category_find((int) $id);
    if (!$category) {
        http_response_code(404);
        view("errors/404", array("title" => "Category not found"));
        return;
    }

    $errors = array();
    $old = $category;

    if (is_post()) {
        verify_csrf();
        $old = category_form_data();
        $errors = validate_category($old);

        if (count($errors) == 0) {
            category_update((int) $id, $old);
            set_flash("success", "Category updated.");
            redirect("/admin/categories");
        }
    }

    view("admin/category_form", array(
        "title" => "Edit Category",
        "category" => $old,
        "errors" => $errors
    ));
}

function admin_delete_category($id)
{
    require_role("admin");
    verify_csrf();

    if (category_has_medicines((int) $id)) {
        set_flash("error", "Cannot delete a category that still has medicines.");
    } else {
        category_delete((int) $id);
        set_flash("success", "Category deleted.");
    }

    redirect("/admin/categories");
}

function admin_medicines()
{
    require_role("admin");

    view("admin/medicines", array(
        "title" => "Medicines",
        "medicines" => medicine_all()
    ));
}

function admin_create_medicine()
{
    require_role("admin");

    $errors = array();
    $old = array();

    if (is_post()) {
        verify_csrf();
        $old = medicine_form_data();
        $errors = validate_medicine($old);
        list($image_path, $upload_error) = upload_file("image", "medicines");

        if ($upload_error) {
            $errors["image"] = $upload_error;
        } else {
            $old["image_path"] = $image_path;
        }

        if (count($errors) == 0) {
            medicine_create($old);
            set_flash("success", "Medicine created.");
            redirect("/admin/medicines");
        }

        if ($image_path) {
            delete_public_file($image_path);
            $old["image_path"] = null;
        }
    }

    view("admin/medicine_form", array(
        "title" => "Add Medicine",
        "medicine" => $old,
        "categories" => category_all(),
        "errors" => $errors,
        "isEdit" => false
    ));
}

function admin_edit_medicine($id)
{
    require_role("admin");

    $medicine = medicine_find((int) $id);
    if (!$medicine) {
        http_response_code(404);
        view("errors/404", array("title" => "Medicine not found"));
        return;
    }

    $errors = array();
    $old = $medicine;

    if (is_post()) {
        verify_csrf();
        $old = array_merge($medicine, medicine_form_data());
        $errors = validate_medicine($old);
        list($image_path, $upload_error) = upload_file("image", "medicines");

        if ($upload_error) {
            $errors["image"] = $upload_error;
        } else {
            $old["image_path"] = $image_path;
        }

        if (count($errors) == 0) {
            medicine_update((int) $id, $old);

            if ($image_path) {
                delete_public_file($medicine["image_path"] ?? null);
            }

            set_flash("success", "Medicine updated.");
            redirect("/admin/medicines");
        }

        if ($image_path) {
            delete_public_file($image_path);
            $old["image_path"] = $medicine["image_path"] ?? null;
        }
    }

    view("admin/medicine_form", array(
        "title" => "Edit Medicine",
        "medicine" => $old,
        "categories" => category_all(),
        "errors" => $errors,
        "isEdit" => true
    ));
}

function admin_delete_medicine($id)
{
    require_role("admin");
    verify_csrf();

    $medicine = medicine_find((int) $id);
    if (!$medicine) {
        set_flash("error", "Medicine not found.");
        redirect("/admin/medicines");
    }

    if (medicine_in_pending_order((int) $id)) {
        set_flash("error", "Cannot delete medicine that is in a pending order.");
        redirect("/admin/medicines");
    }

    if (medicine_in_any_order((int) $id)) {
        set_flash("error", "Cannot delete medicine because it appears in purchase history.");
        redirect("/admin/medicines");
    }

    medicine_delete((int) $id);
    delete_public_file($medicine["image_path"] ?? null);
    set_flash("success", "Medicine deleted.");

    redirect("/admin/medicines");
}

function admin_customers()
{
    require_role("admin");

    view("admin/customers", array(
        "title" => "Customers",
        "customers" => user_all_customers()
    ));
}

function admin_delete_customer($id)
{
    require_role("admin");
    verify_csrf();

    if (user_delete_customer((int) $id)) {
        set_flash("success", "Customer deleted with related cart and orders.");
    } else {
        set_flash("error", "Customer not found.");
    }

    redirect("/admin/customers");
}

function admin_orders()
{
    require_role("admin");

    view("admin/orders", array(
        "title" => "Purchase Requests",
        "orders" => order_all(),
        "deliveryPersons" => user_all_by_role("delivery")
    ));
}

function admin_assign_delivery($id)
{
    require_role("admin");
    verify_csrf();

    $delivery_id = (int) ($_POST["delivery_person_id"] ?? 0);

    if ($delivery_id > 0 && order_assign_delivery((int) $id, $delivery_id)) {
        set_flash("success", "Delivery person assigned.");
    } else {
        set_flash("error", "Could not assign delivery person.");
    }

    redirect("/admin/orders");
}

function admin_history()
{
    require_role("admin");

    view("admin/history", array(
        "title" => "Purchase History",
        "orders" => order_accepted_history()
    ));
}

function category_form_data()
{
    return array(
        "name" => trim($_POST["name"] ?? ""),
        "category_type" => trim($_POST["category_type"] ?? "")
    );
}

function validate_category($data)
{
    $errors = array();

    if ($data["name"] == "" || strlen($data["name"]) > 120) {
        $errors["name"] = "Category name is required and must be under 120 characters.";
    }

    if ($data["category_type"] != "liquid" && $data["category_type"] != "solid") {
        $errors["category_type"] = "Choose liquid or solid.";
    }

    return $errors;
}

function medicine_form_data()
{
    return array(
        "name" => trim($_POST["name"] ?? ""),
        "category_id" => (int) ($_POST["category_id"] ?? 0),
        "vendor_name" => trim($_POST["vendor_name"] ?? ""),
        "price" => trim($_POST["price"] ?? ""),
        "availability" => trim($_POST["availability"] ?? ""),
        "description" => trim($_POST["description"] ?? ""),
        "image_path" => null
    );
}

function validate_medicine($data)
{
    $errors = array();

    if ($data["name"] == "" || strlen($data["name"]) > 150) {
        $errors["name"] = "Medicine name is required and must be under 150 characters.";
    }

    if (!category_find((int) $data["category_id"])) {
        $errors["category_id"] = "Choose a valid category.";
    }

    if ($data["vendor_name"] == "" || strlen($data["vendor_name"]) > 120) {
        $errors["vendor_name"] = "Vendor name is required and must be under 120 characters.";
    }

    if (!is_numeric($data["price"]) || (float) $data["price"] <= 0) {
        $errors["price"] = "Price must be greater than 0.";
    }

    if (!ctype_digit((string) $data["availability"])) {
        $errors["availability"] = "Stock must be 0 or higher.";
    }

    if (strlen($data["description"]) > 1000) {
        $errors["description"] = "Description must be under 1000 characters.";
    }

    return $errors;
}
