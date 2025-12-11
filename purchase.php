<?php

function show_purchase_item(int $item_id, int $price, int $quantity): void {
  require_once "./db.php";
  $mysqli = db_connect();

  $query = "select name from item where id = ?";
  $rows = db_execute($mysqli, $query, "i", [$item_id]);

  db_disconnect($mysqli);

  echo "<li>{$rows[0]["name"]} {$price} 円 x {$quantity}</li>";
}

function show_purchase(int $account_id, int $purchase_id): void {
  require_once "./db.php";
  $mysqli = db_connect();

  $query = "select * from purchase where id = ? and account_id = ?";
  $rows = db_execute($mysqli, $query, "ii", [$purchase_id, $account_id]);

  if (!$rows) {
    exit("購入記録が見つかりません。");
  }

  $date = $rows[0]["date"];
  $total = $rows[0]["total"];

  $query = "select * from purchase_item where purchase_id = ?";
  $rows = db_execute($mysqli, $query, "i", [$purchase_id]);

  db_disconnect($mysqli);

  echo <<<END
  <p>{$date}<p>
  <ul>
  END;

  foreach ($rows as $row) {
    show_purchase_item($row["item_id"], $row["price"], $row["quantity"]);
  }

  echo <<<END
  </ul>
  <p>合計 {$total} 円</p>
  END;
}

require_once "./nav.php";

echo "<h2>購入記録</h2>";

@session_start();

if (!isset($_SESSION["id"])) {
  $next = urlencode($_SERVER["REQUEST_URI"]);
  echo "<p><a href='./login.php?next={$next}'>ログイン</a>してください。</p>";
  exit();
}

if (!isset($_GET["id"])) {
  exit("購入IDが指定されていません。");
}

show_purchase($_SESSION["id"], $_GET["id"]);

if (isset($_GET["success"])) {
  echo <<<END
  <hr>
  <p>決済が完了しました。</p>
  END;
}
