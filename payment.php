<?php

function verify_token(): void {
  @session_start();

  if (!isset($_SESSION["token"]) || @$_POST["token"] != @$_SESSION["token"]) {
    exit("二重送信の可能性があります。処理を中止しました。");
  }
  unset($_SESSION["token"]);
}

function insert_purchase(): int {
  @session_start();
  require_once("./db.php");

  $mysqli = db_connect();

  $query = "insert into purchase(account_id, date, total) values(?, ?, ?)";
  $account_id = $_SESSION["id"];
  date_default_timezone_set("Asia/Tokyo");
  $date = date("Y-m-d H:i:s");
  $total = $_POST["total"];
  db_execute($mysqli, $query, "isi", [$account_id, $date, $total]);

  $query = "select last_insert_id() as id";
  $rows = db_execute($mysqli, $query, "", []);

  db_disconnect($mysqli);

  return $rows[0]["id"];
}

function insert_purchase_item(int $purchase_id, int $item_id, int $price, int $quantity): void {
  require_once("./db.php");

  $mysqli = db_connect();

  $query = "insert into purchase_item(purchase_id, item_id, price, quantity) values(?, ?, ?, ?)";
  db_execute($mysqli, $query, "iiii", [$purchase_id, $item_id, $price, $quantity]);

  db_disconnect($mysqli);
}

verify_token();

$purchase_id = insert_purchase();

foreach ($_POST["ids"] as $item_id) {
  insert_purchase_item($purchase_id, $item_id, $_POST["prices"][$item_id], $_POST["quantities"][$item_id]);
}

$_SESSION["cart"] = [];

header("Location: ./purchase.php?id={$purchase_id}&success");
exit;

?>
