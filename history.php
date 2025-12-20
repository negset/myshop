<?php

const PAGE_SIZE = 10;

function get_purchase_count(): int {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select count(*) as cnt from purchase where account_id = ?";
  $rows = db_execute($mysqli, $query, "i", [$_SESSION["id"]]);

  db_disconnect($mysqli);

  return $rows[0]["cnt"];
}

function show_purchases(int $current_page): void {
  @session_start();
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select * from purchase where account_id = ? order by date desc limit ? offset ?";
  $account_id = $_SESSION["id"];
  $limit = PAGE_SIZE;
  $offset = PAGE_SIZE * ($current_page - 1);
  $rows = db_execute($mysqli, $query, "iii", [$account_id, $limit, $offset]);

  db_disconnect($mysqli);

  echo "<ul>";
  foreach ($rows as $row) {
    $total = number_format($row["total"]);
    echo <<<END
    <li><p>
      <a href="./purchase.php?id={$row["id"]}">{$row["date"]}<br>{$total} 円</a>
    </p></li>
    END;
  }
  echo "</ul><br>";
}

@session_start();

require_once "./pagination.php";
require_once "./nav.php";

echo "<h2>購入履歴</h2>";

if (!isset($_SESSION["id"])) {
  $next = urlencode($_SERVER["REQUEST_URI"]);
  echo "<p><a href='./login.php?next={$next}'>ログイン</a>してください。</p>";
  exit();
}

$purchase_count = get_purchase_count();
$current_page = $_GET["page"] ?? 1;
$max_page = max(1, ceil($purchase_count / PAGE_SIZE));

echo "<p>全 {$purchase_count} 件</p>";
show_purchases($current_page);
show_pagination($current_page, $max_page);

?>