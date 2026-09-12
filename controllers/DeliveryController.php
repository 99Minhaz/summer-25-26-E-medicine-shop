<?php

function delivery_dashboard()
{
    require_role("delivery");

    view("delivery/dashboard", array(
        "title" => "Delivery Dashboard",
        "orders" => order_all_for_delivery(current_user_id())
    ));
}

function delivery_mark_delivered($id)
{
    require_role("delivery");
    verify_csrf();

    if (order_mark_delivered((int) $id, current_user_id())) {
        set_flash("success", "Order marked as delivered.");
    } else {
        set_flash("error", "Could not update this order.");
    }

    redirect("/delivery");
}
