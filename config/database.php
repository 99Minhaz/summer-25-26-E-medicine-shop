<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "E_medicine_shop";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

function db()
{
    global $conn;
    return $conn;
}

function db_connection_only()
{
    global $conn;
    return $conn;
}

function db_select($sql, $types = "", $values = array())
{
    $conn = db();
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL prepare failed: " . mysqli_error($conn));
    }

    if ($types != "") {
        $refs = array();
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$refs);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("SQL execute failed: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $rows = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $rows;
}

function db_one($sql, $types = "", $values = array())
{
    $rows = db_select($sql, $types, $values);

    if (count($rows) > 0) {
        return $rows[0];
    }

    return null;
}

function db_run($sql, $types = "", $values = array())
{
    $conn = db();
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL prepare failed: " . mysqli_error($conn));
    }

    if ($types != "") {
        $refs = array();
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$refs);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("SQL execute failed: " . mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
}

// db_call() / db_call_one() / db_call_run() are dedicated helpers for
// executing stored procedures (CALL ...). MySQL sends an extra "end of
// procedure" packet after a CALL that db_run()/db_select() do not drain,
// which can desync the connection for the next query. These helpers
// drain that extra packet safely via mysqli_next_result().
function db_call($sql, $types = "", $values = array())
{
    $conn = db();
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL prepare failed: " . mysqli_error($conn));
    }

    if ($types != "") {
        $refs = array();
        foreach ($values as $key => $value) {
            $refs[$key] = &$values[$key];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$refs);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("SQL execute failed: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $rows = array();

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    mysqli_stmt_close($stmt);

    while (mysqli_more_results($conn)) {
        mysqli_next_result($conn);
    }

    return $rows;
}

function db_call_one($sql, $types = "", $values = array())
{
    $rows = db_call($sql, $types, $values);
    return count($rows) > 0 ? $rows[0] : null;
}

function db_call_run($sql, $types = "", $values = array())
{
    db_call($sql, $types, $values);
}
