<?php

function show_item(int $id, int $quantity): void {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select * from item where id = ?";
  $rows = db_execute($mysqli, $query, "i", [$id]);

  db_disconnect($mysqli);

  echo <<<END
  <input type="hidden" name="ids[]" value="{$id}">
  {$rows[0]["name"]} <input name="prices[{$id}]" value="{$rows[0]["price"]}" readonly> 円 x <input name="quantities[{$id}]" value="{$quantity}" readonly>
  END;
}

function get_price(int $id): int {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select price from item where id = ?";
  $rows = db_execute($mysqli, $query, "i", [$id]);

  db_disconnect($mysqli);

  return $rows[0]["price"];
}

@session_start();

if (isset($_POST["clear"])) {
  $_SESSION["cart"] = [];
}

require_once "./nav.php";

echo "<h2>カート内容</h2>";

if (empty($_SESSION["cart"])) {
  echo "<p>カートは空です。</p>";
} else {
  echo <<<END
  <form method="post" action="?">
    <ul>
  END;

  $total_price = 0;
  foreach ($_SESSION["cart"] as $id => $quantity) {
    echo "<li>";
    show_item($id, $quantity);
    echo "</li>";

    $total_price += $quantity * get_price($id);
  }

  $token = bin2hex(random_bytes(32));
  $_SESSION["token"] = $token;

  echo <<<END
    </ul>
    <p>合計 <input type="text" name="total" value="{$total_price}" readonly> 円</p>
    <button type="submit" formaction="./payment.php">決済に進む</button>
    <button type="submit" name="clear" formaction="{$_SERVER['PHP_SELF']}">カートをクリア</button>
    <input type="hidden" name="token" value="{$token}">
  </form>
  END;
}

?>