<?php

function show_page_link(int $page_num, bool $is_current_page): void {
  if ($is_current_page) {
    echo " <a>$page_num</a>";
  } else {
    $parsed = parse_url($_SERVER["REQUEST_URI"]);
    parse_str(@$parsed["query"], $query);
    $query["page"] = $page_num;
    $query = http_build_query($query);
    $url = "{$parsed['path']}?{$query}";
    echo " <a href='{$url}'>{$page_num}</a>";
  }
}

function show_pagination(int $current_page, int $max_page): void {
  echo "ページ";
  if ($current_page < 4) {
    for ($i = 1; $i <= min(5, $max_page); $i++) {
      show_page_link($i, $i == $current_page);
    }
    if ($max_page > 5) echo " ...";
  } else {
    show_page_link(1, false);
    echo " ...";
    for ($i = min($current_page - 1, $max_page - 3); $i <= min($current_page + 2, $max_page); $i++) {
      show_page_link($i, $i == $current_page);
    }
    if ($max_page > $current_page + 2) echo " ...";
  }
}

?>