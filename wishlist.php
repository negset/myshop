<?php

const PAGE_SIZE = 10;

function get_item_count(): int {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select count(*) as cnt from wishlist where account_id = ?";
  $rows = db_execute($mysqli, $query, "i", [$_SESSION["id"]]);

  db_disconnect($mysqli);

  return $rows[0]["cnt"];
}

function show_items(int $current_page): void {
  @session_start();
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select item_id from wishlist where account_id = ? limit ? offset ?";
  $account_id = $_SESSION["id"];
  $limit = PAGE_SIZE;
  $offset = PAGE_SIZE * ($current_page - 1);
  $rows = db_execute($mysqli, $query, "iii", [$account_id, $limit, $offset]);

  echo <<<END
  <form method="post" action="{$_SERVER["PHP_SELF"]}">
  <ul>
  END;

  foreach ($rows as $row) {
    $query = "select name from item where id = ?";
    $name = db_execute($mysqli, $query, "i", [$row["item_id"]])[0]["name"];

    echo <<<END
    <li>
      <p><a href="./item.php?id={$row["item_id"]}">$name</a></p>
      <button type="submit" name="delete" value="{$row["item_id"]}">削除</button>
    </li>
    END;
  }

  echo <<<END
    </ul>
  </form>
  END;

  db_disconnect($mysqli);
}

@session_start();

if (isset($_SESSION["id"]) && isset($_POST["delete"])) {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "delete from wishlist where account_id = ? and item_id = ?";
  db_execute($mysqli, $query, "ii", [$_SESSION["id"], $_POST["delete"]]);

  db_disconnect($mysqli);
}

require_once "./pagination.php";
require_once "./nav.php";

echo "<h2>ウィッシュリスト</h2>";

if (!isset($_SESSION["id"])) {
  $next = urlencode($_SERVER["REQUEST_URI"]);
  echo "<p><a href='./login.php?next={$next}'>ログイン</a>してください。</p>";
  exit();
}

$item_count = get_item_count();
$current_page = $_GET["page"] ?? 1;
$max_page = max(1, ceil($item_count / PAGE_SIZE));

echo "<p>全 {$item_count} 件</p>";
show_items($current_page);
show_pagination($current_page, $max_page);

?>