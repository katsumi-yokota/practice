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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Logout</title>
</head>
<body>
  <div class="container text-center">
    <h1 class="my-5">ログアウト</h1>
    <div class="bg-light my-5 py-5">
     <p class="">ログアウトしました。またのご利用をお待ちしております。</p>
     <p class="">エントリー一覧のご利用を続ける場合は「ログインする」ボタンをクリックして、遷移先でログインしてください。</p>
    </div>
    <a href="login.php" class="btn btn-success btn-lg px-5">ログインする</a>
  </div>
</body>
</html>
