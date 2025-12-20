<?php

const PAGE_SIZE = 10;
const SORT_TYPES = [
  "id-asc" => "商品ID 昇順",
  "id-desc" => "商品ID 降順",
  "price-asc" => "価格が安い順",
  "price-desc" => "価格が高い順"
];

function get_item_count(string $search_query): int {
  require_once "./db.php";

  $mysqli = db_connect();

  $query = "select count(*) as cnt from item where name like ?";
  $like = "%{$search_query}%";
  $rows = db_execute($mysqli, $query, "s", [$like]);

  db_disconnect($mysqli);

  return $rows[0]["cnt"];
}

function show_items(string $search_query, int $current_page): void {
  require_once "./db.php";

  $mysqli = db_connect();


  $order = explode("-", array_key_exists(@$_GET["sort"], SORT_TYPES) ? $_GET["sort"] : "id-asc");
  $query = "select * from item where name like ? order by {$order[0]} {$order[1]} limit ? offset ?";
  $like = "%{$search_query}%";
  $limit = PAGE_SIZE;
  $offset = PAGE_SIZE * ($current_page - 1);
  $rows = db_execute($mysqli, $query, "sii", [$like, $limit, $offset]);

  echo "<ul>";
  foreach ($rows as $row) {
    $price = number_format($row["price"]);
    echo <<<END
    <li><p>
      <a href="./item.php?id={$row["id"]}">{$row["name"]}<br>{$price} 円</a>
    </p></li>
    END;
  }
  echo "</ul><br>";

  db_disconnect($mysqli);
}

require_once "./pagination.php";
require_once "./nav.php";

$search_query = htmlspecialchars(@$_GET['search'], ENT_QUOTES);

echo <<<END
<form method="get" action="{$_SERVER['PHP_SELF']}">
  <input type="text" name="search" value="{$search_query}"></input>
  <select name="sort">
END;

foreach (SORT_TYPES as $sort => $name) {
  $selected = @$_GET["sort"] == $sort ? "selected" : "";
  echo "<option value='{$sort}' {$selected}>{$name}</option>";
}

echo <<<END
  </select>
  <button type="submit">検索</button>
</form>
END;

$current_page = $_GET["page"] ?? 1;
$item_count = get_item_count($search_query);

echo "{$item_count} 件のヒット";

show_items($search_query, $current_page);

$max_page = max(1, ceil($item_count / PAGE_SIZE));
show_pagination($current_page, $max_page);

?>