<?php

const RATE_LIMIT = 5;
const RATE_WINDOW = 60;

function login(): string {
  @session_start();

  require_once "./db.php";
  $mysqli = db_connect();

  $ip = $_SERVER["REMOTE_ADDR"];

  $query = "select count(*) as cnt from login_failure where ip = ? and time > now() - interval ? second";
  $rows = db_execute($mysqli, $query, "si", [$ip, RATE_WINDOW]);

  if ($rows[0]["cnt"] >= RATE_LIMIT) {
    db_disconnect($mysqli);
    return "試行回数が多すぎます。しばらく時間をおいてお試しください。";
  }

  $query = "select id, password from account where user = ?";
  $rows = db_execute($mysqli, $query, "s", [$_POST["user"]]);

  if ($rows && password_verify($_POST["password"], $rows[0]["password"])) {
    $_SESSION["id"] = $rows[0]["id"];
    $_SESSION["user"] = $_POST["user"];

    $query = "delete from login_failure where ip = ?";
    db_execute($mysqli, $query, "s", [$ip]);

    db_disconnect($mysqli);

    redirect();
  }

  $query = "insert into login_failure(ip) values(?)";
  db_execute($mysqli, $query, "s", [$ip]);

  db_disconnect($mysqli);

  return "ログインに失敗しました。";
}

function signup(): string {
  if ($_POST["password"] != $_POST["password_check"]) {
    return "パスワードが一致しません。";
  }
  $length = strlen($_POST["password"]);
  if ($length < 8 || $length > 32) {
    return "パスワードは 8 ~ 64 文字に設定してください。";
  }

  require_once "./db.php";
  $mysqli = db_connect();

  $query = "select count(*) as cnt from account where user = ?";
  $user = $_POST["user"];
  $rows = db_execute($mysqli, $query, "s", [$user]);
  if ($rows && $rows[0]["cnt"] > 0) {
    db_disconnect($mysqli);
    return "そのユーザ名は使用できません。";
  }

  $query = "insert into account(user, password) values(?, ?)";
  $user = $_POST["user"];
  $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
  db_execute($mysqli, $query, "ss", [$user, $password]);

  db_disconnect($mysqli);

  return "アカウントを作成しました。";
}

function redirect(): void {
  $url = @$_GET["next"];
  $parsed = parse_url($url);

  if (!$url || @$parsed["host"] && $_SERVER["HTTP_HOST"] != $parsed["host"]) {
    $url = "./";
  }

  header("Location: {$url}");
  exit();
}

if (isset($_POST["login"])) {
  $msg = login();
} else if (isset($_POST["signup"])) {
  $msg = signup();
}

require_once "./nav.php";

if (@$_GET["act"] != "logout" && @$_SESSION["id"]) {
  echo "<p>ログイン済みです。</p>";
  exit();
}

switch (@$_GET["act"]) {
  case "signup":
    $parsed = parse_url($_SERVER["REQUEST_URI"]);
    parse_str(@$parsed["query"], $query);
    unset($query["act"]);
    $query = http_build_query($query);
    $login_url = "{$parsed['path']}?{$query}";

    echo <<<END
    <ul>
      <li>パスワードは 8 ~ 64 文字で、半角英数字記号が使用できます。</li>
    </ul>
    <form method="post" action="{$_SERVER['REQUEST_URI']}">
      <p><label>
        ユーザ名: 
        <input type="text" name="user" required>
      </label></p>
      <p><label>
        パスワード: 
        <input type="password" name="password" required>
      </label></p>
      <p><label>
        パスワード (確認用): 
        <input type="password" name="password_check" required>
      </label></p>
      <button type="submit" name="signup">アカウント作成</button>
    </form>
    <a href="{$login_url}">既存のアカウントを使用する</a>
    END;
    break;

  case "logout":
    @session_start();
    $_SESSION = [];

    redirect();
    break;

  default:
    $parsed = parse_url($_SERVER["REQUEST_URI"]);
    parse_str(@$parsed["query"], $query);
    $query["act"] = "signup";
    $query = http_build_query($query);
    $signup_url = "{$parsed['path']}?{$query}";

    echo <<<END
    <form method="post" action="{$_SERVER['REQUEST_URI']}">
      <p><label>
        ユーザ名: 
        <input type="text" name="user" required>
      </label></p>
      <p><label>
        パスワード: 
        <input type="password" name="password" required>
      </label></p>
      <button type="submit" name="login">ログイン</button>
    </form>
    <a href="{$signup_url}">アカウントを新規作成する</a>
    END;
    break;
}

if (isset($msg)) {
  echo <<<END
  <hr>
  <p>{$msg}</p>
  END;
}

?>
