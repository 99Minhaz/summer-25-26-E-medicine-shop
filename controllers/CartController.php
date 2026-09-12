<?php

function cart_index()
{
    require_role("customer");

    view("cart/index", array(
        "title" => "Cart",
        "items" => cart_items(current_user_id()),
        "total" => cart_total(current_user_id())
    ));
}
