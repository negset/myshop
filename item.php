<?php

@session_start();

if (isset($_POST["add-cart"]) && isset($_SESSION["id"])) {
  @$_SESSION["cart"][$_GET["id"]] += (int) $_POST["quantity"];
}

if (isset($_POST["add-list"]) && isset($_SESSION["id"])) {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "insert into wishlist(account_id, item_id) values(?, ?)";
  $rows = db_execute($mysqli, $query, "ii", [$_SESSION["id"], $_GET["id"]]);

  db_disconnect($mysqli);
}

require_once "./nav.php";

if (!isset($_GET["id"])) {
  exit("商品IDが指定されていません。");
}

require_once "./db.php";

$mysqli = db_connect();

$query = "select * from item where id = ?";
$rows = db_execute($mysqli, $query, "i", [$_GET["id"]]);

db_disconnect($mysqli);

if (!$rows) {
  exit("商品が見つかりません。");
}

$price = number_format($rows[0]["price"]);
$initial_quantity = @$_POST["quantity"] ?? 1;
echo <<<END
<form method="post" action="{$_SERVER['REQUEST_URI']}">
  <h2>{$rows[0]["name"]}</h2>
  <p>{$rows[0]["description"]}</p>
  <p>{$price} 円</p>
  <p>
    <input type="number" name="quantity" min="1" value="{$initial_quantity}">
    <label for="quantity">個</label>
  </p>
  <button type="submit" name="add-cart">カートに追加</button>
  <button type="submit" name="add-list">リストに追加</button>
</form>

END;

if (isset($_POST["quantity"])) {
  if (!isset($_SESSION["id"])) {
    $next = urlencode($_SERVER["REQUEST_URI"]);
    echo "<p><a href='./login.php?next={$next}'>ログイン</a>してください。</p>";
  } else if (isset($_POST["add-cart"])) {
    echo <<<END
    <hr>
    <p>商品をカートに追加しました。</p>
    <p><a href="./cart.php">レジに進む</a></p>
    END;
  } else if (isset($_POST["add-list"])) {
    echo <<<END
    <hr>
    <p>商品をウィッシュリストに追加しました。</p>
    <p><a href="./wishlist.php">リストを確認する</a></p>
    END;
  }
}

?>