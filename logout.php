<?php
session_start();
session_destroy();
http_response_code(200);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログアウト</title>
</head>
<body>
  <p>ログアウトしました。</p>
  <a href="login.php">ログイン</a>
</body>
</html>
