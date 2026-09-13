<?php
// Author: Minhaz Hassan — Admin Module
function medicine_all($filters = array())
{
    $sql = "SELECT medicines.*, categories.name AS category_name, categories.category_type
            FROM medicines
            JOIN categories ON categories.id = medicines.category_id
            WHERE 1 = 1";
    $types = "";
    $values = array();

    if (isset($filters["q"]) && $filters["q"] != "") {
        $sql .= " AND medicines.name LIKE ?";
        $types .= "s";
        $values[] = "%" . $filters["q"] . "%";
    }

    if (isset($filters["vendor"]) && $filters["vendor"] != "") {
        $sql .= " AND medicines.vendor_name LIKE ?";
        $types .= "s";
        $values[] = "%" . $filters["vendor"] . "%";
    }

    if (isset($filters["genre"]) && $filters["genre"] != "") {
        $sql .= " AND medicines.category_id = ?";
        $types .= "i";
        $values[] = (int) $filters["genre"];
    }

    if (isset($filters["type"]) && ($filters["type"] == "liquid" || $filters["type"] == "solid")) {
        $sql .= " AND categories.category_type = ?";
        $types .= "s";
        $values[] = $filters["type"];
    }

    if (isset($filters["category_id"]) && $filters["category_id"] != "") {
        $sql .= " AND medicines.category_id = ?";
        $types .= "i";
        $values[] = (int) $filters["category_id"];
    }

    $sql .= " ORDER BY medicines.created_at DESC, medicines.name ASC";
    return db_select($sql, $types, $values);
}

function medicine_find($id)
{
    return db_one(
        "SELECT medicines.*, categories.name AS category_name, categories.category_type
         FROM medicines
         JOIN categories ON categories.id = medicines.category_id
         WHERE medicines.id = ?
         LIMIT 1",
        "i",
        array($id)
    );
}

function medicine_create($data)
{
    db_run(
        "INSERT INTO medicines (name, category_id, vendor_name, vendor_id, price, availability, description, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        "sisidiss",
        array(
            $data["name"],
            $data["category_id"],
            $data["vendor_name"],
            $data["vendor_id"] ?? null,
            $data["price"],
            $data["availability"],
            $data["description"],
            $data["image_path"]
        )
    );
}

function medicine_update($id, $data)
{
    if ($data["image_path"]) {
        db_run(
            "UPDATE medicines
             SET name = ?, category_id = ?, vendor_name = ?, price = ?, availability = ?, description = ?, image_path = ?
             WHERE id = ?",
            "sisdissi",
            array(
                $data["name"],
                $data["category_id"],
                $data["vendor_name"],
                $data["price"],
                $data["availability"],
                $data["description"],
                $data["image_path"],
                $id
            )
        );
    } else {
        db_run(
            "UPDATE medicines
             SET name = ?, category_id = ?, vendor_name = ?, price = ?, availability = ?, description = ?
             WHERE id = ?",
            "sisdisi",
            array(
                $data["name"],
                $data["category_id"],
                $data["vendor_name"],
                $data["price"],
                $data["availability"],
                $data["description"],
                $id
            )
        );
    }
}

function medicine_delete($id)
{
    db_run("DELETE FROM medicines WHERE id = ?", "i", array($id));
}

function medicine_in_pending_order($id)
{
    $row = db_one(
        "SELECT COUNT(*) AS total
         FROM order_items
         JOIN orders ON orders.id = order_items.order_id
         WHERE order_items.medicine_id = ? AND orders.status = 'pending'",
        "i",
        array($id)
    );

    return (int) $row["total"] > 0;
}

function medicine_in_any_order($id)
{
    $row = db_one(
        "SELECT COUNT(*) AS total FROM order_items WHERE medicine_id = ?",
        "i",
        array($id)
    );

    return (int) $row["total"] > 0;
}

function medicine_find_for_vendor($id, $vendor_id)
{
    return db_one(
        "SELECT medicines.*, categories.name AS category_name, categories.category_type
         FROM medicines
         JOIN categories ON categories.id = medicines.category_id
         WHERE medicines.id = ? AND medicines.vendor_id = ?
         LIMIT 1",
        "ii",
        array($id, $vendor_id)
    );
}

function medicine_all_by_vendor($vendor_id)
{
    return db_select(
        "SELECT medicines.*, categories.name AS category_name, categories.category_type
         FROM medicines
         JOIN categories ON categories.id = medicines.category_id
         WHERE medicines.vendor_id = ?
         ORDER BY medicines.created_at DESC",
        "i",
        array($vendor_id)
    );
}

function medicine_count_by_vendor($vendor_id)
{
    $row = db_one("SELECT COUNT(*) AS total FROM medicines WHERE vendor_id = ?", "i", array($vendor_id));
    return (int) $row["total"];
}

function medicine_vendors()
{
    $rows = db_select("SELECT DISTINCT vendor_name FROM medicines ORDER BY vendor_name");
    $vendors = array();

    foreach ($rows as $row) {
        $vendors[] = $row["vendor_name"];
    }

    return $vendors;
}

function medicine_count()
{
    $row = db_one("SELECT COUNT(*) AS total FROM medicines");
    return (int) $row["total"];
}
