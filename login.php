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
$inputToken = filter_input(INPUT_POST, 'token');
$sessionToken = $_SESSION['token'] ?? '';
if ($sessionToken !== $inputToken && filter_input(INPUT_SERVER,'REQUEST_METHOD') === 'POST') 
{
  $errorMessage = '不正なリクエストです';
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
  elseif (filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'GET')
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Login</title>
</head>
<body>
  <div class="container">
    <h1 class="text-center py-5">
      エントリー一覧にログインする
    </h1>
    <?php if(isset($errorMessage)): ?>
    <p class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <form action="" method="post">
      <label for="username" class="fw-bold">ユーザー名</label>
      <input type="text" name="username" id="username" class="form-control my-2" required value="<?php echo htmlspecialchars($username); ?>">
      <label for="password" class="fw-bold">パスワード</label>
      <input type="password" name="password" id="password" class="form-control my-2" required value="">
      <input type="hidden" name="token" value="<?php if (isset($_SESSION['token'])) {echo htmlspecialchars($_SESSION['token']);} ?>">
      <input type="submit" class="btn btn-success btn-lg px-5 my-2" value="ログイン">
    </form>
  </div>
</body>
</html>
