<?php

function home_index()
{
    $filters = array(
        "q" => trim($_GET["q"] ?? ""),
        "vendor" => trim($_GET["vendor"] ?? ""),
        "genre" => trim($_GET["genre"] ?? ""),
        "type" => trim($_GET["type"] ?? "")
    );

    view("home/index", array(
        "title" => "Home",
        "categories" => category_all(),
        "medicines" => medicine_all($filters),
        "vendors" => medicine_vendors(),
        "filters" => $filters,
        "activeCategory" => null
    ));
}

function home_category($id)
{
    $category = category_find((int) $id);

    if (!$category) {
        http_response_code(404);
        view("errors/404", array("title" => "Category not found"));
        return;
    }

    $filters = array(
        "category_id" => (int) $id,
        "type" => trim($_GET["type"] ?? ""),
        "q" => trim($_GET["q"] ?? ""),
        "vendor" => trim($_GET["vendor"] ?? "")
    );

    view("home/index", array(
        "title" => $category["name"],
        "categories" => category_all(),
        "medicines" => medicine_all($filters),
        "vendors" => medicine_vendors(),
        "filters" => $filters,
        "activeCategory" => $category
    ));
}
