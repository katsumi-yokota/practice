<?php
session_start();

// DBに接続
$dbUser = 'yamadasan';
$dbPass = '1q2w3e4r5t';
$dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
$pdo = new PDO($dsn, $dbUser, $dbPass);

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');

// CSRF対策
$token = filter_input(INPUT_POST, 'token');
if (!isset($token) || $_SESSION['token'] !== $token) 
{
  $errorMessage = '不正なリクエストはやめてください';
  http_response_code(400);
}
else
{
  $stmt = $pdo->prepare('SELECT * FROM login WHERE username = :username AND password = :password');
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->bindValue(':password', $password, PDO::PARAM_STR);
  $stmt->execute();

  // ユーザー名とパスワードのチェック
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($result !== false)
  {
    $_SESSION['username'] = $result['username'];

    http_response_code(200);
    header('Location: entries.php');
    exit;
  }
  else
  {
    $errorMessage = '正しいユーザー名またはパスワードを入力してください';
    http_response_code(400);
  }
}
// トークン生成
$_SESSION['token'] = uniqid();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>
<body>
  <h1>
    ログイン機能
  </h1>
  <p><?php echo $errorMessage; ?></p>
  <form action="" method="post">
    <label for="username">ユーザー名</label>
    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>">
    <label for="password">パスワード</label>
    <input type="password" name="password" id="password" value="">
    <input type="hidden" name="token" value="<?php if (isset($_SESSION['token'])) {echo htmlspecialchars($_SESSION['token']);} ?>">
    <input type="submit">
  </form>
</body>
</html>
