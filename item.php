<?php

@session_start();

if (isset($_POST["quantity"]) && isset($_SESSION["id"])) {
  @$_SESSION["cart"][$_GET["id"]] += (int) $_POST["quantity"];
}

require_once "./nav.php";

if (!isset($_GET["id"])) {
  exit("商品IDが指定されていません。");
}

require_once "./db.php";

$mysqli = db_connect();

$query = "select * from item where id = ?";
$id = $_GET["id"];
$rows = db_execute($mysqli, $query, "i", [$id]);

db_disconnect($mysqli);

if (!$rows) {
  exit("商品が見つかりません。");
}

$initial_quantity = @$_POST["quantity"] ?? 1;
echo <<<END
<form method="post" action="{$_SERVER['REQUEST_URI']}">
<h2>{$rows[0]["name"]}</h2>
<p>{$rows[0]["description"]}</p>
<p>
  <input type="number" name="quantity" min="1" value="{$initial_quantity}">
  <label for="quantity">個</label>
</p>
<button type="submit">カートに追加</button>
</form>

END;

if (isset($_POST["quantity"])) {
  if (!isset($_SESSION["id"])) {
    $next = urlencode($_SERVER["REQUEST_URI"]);
    echo "<p><a href='./login.php?next={$next}'>ログイン</a>してください。</p>";
  } else {
    echo <<<END
    <hr>
    <p>商品をカートに追加しました。</p>
    <p><a href="./cart.php">レジに進む</a></p>
    END;
  }
}

?>