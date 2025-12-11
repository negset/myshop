<h1><a href="./">MyShop</a></h1>

<?php

@session_start();

if (isset($_SESSION["id"])) {
  $cart_size = count($_SESSION["cart"] ?? []);
  $next = urlencode($_SERVER["REQUEST_URI"]);

  echo <<<END
  こんにちは、{$_SESSION["user"]} さん。
  <br>
  <a href="./cart.php">カート ({$cart_size})</a>
  <a href="./history.php">購入履歴</a>
  <a href="./login.php?act=logout&next={$next}">ログアウト</a>
  END;
} else if (realpath($_SERVER["DOCUMENT_ROOT"] . $_SERVER["PHP_SELF"]) != realpath("./login.php")) {
  $next = urlencode($_SERVER["REQUEST_URI"]);
  echo "<a href='./login.php?next={$next}'>ログイン</a>";
}

echo "<hr>";

?>