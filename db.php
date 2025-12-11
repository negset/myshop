<?php

const MYSQL_SERVER = "localhost:3336";

mysqli_report(MYSQLI_REPORT_ERROR);

function db_connect(): mysqli {
  return new mysqli(MYSQL_SERVER, "root", "", "myshop");
}

function db_disconnect(mysqli $mysqli) {
  $mysqli->close();
}

function db_execute(mysqli $mysqli, string $query, string $types, array $params): ?array {
  $stmt = $mysqli->prepare($query);
  if ($params)
    $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : null;
  $stmt->close();
  return $rows;
}

?>