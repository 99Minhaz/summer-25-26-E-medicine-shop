<?php

function category_all($type = "")
{
    if ($type == "liquid" || $type == "solid") {
        return db_select(
            "SELECT categories.*, COUNT(medicines.id) AS medicine_count
             FROM categories
             LEFT JOIN medicines ON medicines.category_id = categories.id
             WHERE categories.category_type = ?
             GROUP BY categories.id, categories.name, categories.category_type, categories.created_at
             ORDER BY categories.category_type, categories.name",
            "s",
            array($type)
        );
    }

    return db_select(
        "SELECT categories.*, COUNT(medicines.id) AS medicine_count
         FROM categories
         LEFT JOIN medicines ON medicines.category_id = categories.id
         GROUP BY categories.id, categories.name, categories.category_type, categories.created_at
         ORDER BY categories.category_type, categories.name"
    );
}

function category_find($id)
{
    return db_one("SELECT * FROM categories WHERE id = ? LIMIT 1", "i", array($id));
}

function category_create($data)
{
    db_call_run(
        "CALL sp_category_create(?, ?)",
        "ss",
        array($data["name"], $data["category_type"])
    );
}

function category_update($id, $data)
{
    db_run(
        "UPDATE categories SET name = ?, category_type = ? WHERE id = ?",
        "ssi",
        array($data["name"], $data["category_type"], $id)
    );
}

function category_delete($id)
{
    db_run("DELETE FROM categories WHERE id = ?", "i", array($id));
}

function category_has_medicines($id)
{
    $row = db_one("SELECT COUNT(*) AS total FROM medicines WHERE category_id = ?", "i", array($id));
    return (int) $row["total"] > 0;
}

function category_count()
{
    $row = db_one("SELECT COUNT(*) AS total FROM categories");
    return (int) $row["total"];
}
