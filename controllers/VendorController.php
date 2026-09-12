<?php

function vendor_dashboard()
{
    require_role("vendor");

    view("vendor/dashboard", array(
        "title" => "Vendor Dashboard",
        "counts" => array(
            "medicines" => medicine_count_by_vendor(current_user_id())
        )
    ));
}

function vendor_medicines()
{
    require_role("vendor");

    view("vendor/medicines", array(
        "title" => "My Medicines",
        "medicines" => medicine_all_by_vendor(current_user_id())
    ));
}

function vendor_create_medicine()
{
    require_role("vendor");

    $errors = array();
    $old = array();

    if (is_post()) {
        verify_csrf();
        $old = medicine_form_data();
        $old["vendor_name"] = current_user_name();
        $errors = validate_medicine($old);
        list($image_path, $upload_error) = upload_file("image", "medicines");

        if ($upload_error) {
            $errors["image"] = $upload_error;
        } else {
            $old["image_path"] = $image_path;
        }

        if (count($errors) == 0) {
            $old["vendor_id"] = current_user_id();
            medicine_create($old);
            set_flash("success", "Medicine added.");
            redirect("/vendor/medicines");
        }

        if ($image_path) {
            delete_public_file($image_path);
            $old["image_path"] = null;
        }
    }

    view("vendor/medicine_form", array(
        "title" => "Add Medicine",
        "medicine" => $old,
        "categories" => category_all(),
        "errors" => $errors,
        "isEdit" => false
    ));
}

function vendor_edit_medicine($id)
{
    require_role("vendor");

    $medicine = medicine_find_for_vendor((int) $id, current_user_id());
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
        $old["vendor_name"] = current_user_name();
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
            redirect("/vendor/medicines");
        }

        if ($image_path) {
            delete_public_file($image_path);
            $old["image_path"] = $medicine["image_path"] ?? null;
        }
    }

    view("vendor/medicine_form", array(
        "title" => "Edit Medicine",
        "medicine" => $old,
        "categories" => category_all(),
        "errors" => $errors,
        "isEdit" => true
    ));
}

function vendor_delete_medicine($id)
{
    require_role("vendor");
    verify_csrf();

    $medicine = medicine_find_for_vendor((int) $id, current_user_id());
    if (!$medicine) {
        set_flash("error", "Medicine not found.");
        redirect("/vendor/medicines");
    }

    if (medicine_in_pending_order((int) $id)) {
        set_flash("error", "Cannot delete medicine that is in a pending order.");
        redirect("/vendor/medicines");
    }

    if (medicine_in_any_order((int) $id)) {
        set_flash("error", "Cannot delete medicine because it appears in purchase history.");
        redirect("/vendor/medicines");
    }

    medicine_delete((int) $id);
    delete_public_file($medicine["image_path"] ?? null);
    set_flash("success", "Medicine deleted.");

    redirect("/vendor/medicines");
}
