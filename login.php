<?php
session_start();
echo $_SERVER['REQUEST_METHOD'];

// DBに接続
$dbUser = 'yamadasan';
$dbPass = '1q2w3e4r5t';
$dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
$pdo = new PDO($dsn, $dbUser, $dbPass);

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');

// ログイン試行回数の制限のテスト
$attempts = 1;
$attempts = filter_input(INPUT_POST, 'attempts');
$_SESSION['submit'] = '';
$_SESSION['first_attemptted_at'] = '';
if (filter_input(INPUT_POST, 'submit'))
{
  $_SESSION['submit'] = (int)$attempts++;
}
$_SESSION['first_attemptted_at'] = '';
$firstAtempttedAt = '';
if (isset($_SESSION['first_attemptted_at']) && filter_input(INPUT_POST, 'submit'))
{
  if ($_SESSION['submit'] === 0)
  {
    $_SESSION['first_attemptted_at'] = date('YmdHis');
  }
  if ($_SESSION['submit'] !== 0)
  {
    $_SESSION['first_attemptted_at'];
  }
}
$_SESSION['last_attemptted_at'] = '';
if (isset($_SESSION['last_attemptted_at']) && filter_input(INPUT_POST, 'submit'))
{
  $_SESSION['last_attemptted_at'] = date('YmdHis');
}

$stmt = $pdo->prepare('SELECT * FROM login WHERE username = :username');
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
if ($stmt->fetchAll() !== false)
{
  $stmt3 = $pdo->prepare('UPDATE login_attempts SET count = :count, first_attemptted_at = :first_attemptted_at, last_attemptted_at = :last_attemptted_at WHERE username = :username');
  $stmt3->bindValue(':count', $_SESSION['submit'], PDO::PARAM_INT);
  $stmt3->bindValue(':first_attemptted_at', $_SESSION['first_attemptted_at'], PDO::PARAM_INT);
  $stmt3->bindValue(':last_attemptted_at', $_SESSION['last_attemptted_at'], PDO::PARAM_INT);
  $stmt3->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt3->execute();
}
$stmt4 = $pdo->prepare('SELECT * FROM login_attempts WHERE count = :count');
$stmt4->bindValue(':count', $_SESSION['submit'], PDO::PARAM_INT);
$stmt4->execute();

$limitLoginMessage = '';
$result = $stmt4->fetch(PDO::FETCH_ASSOC);
if ((int)$_SESSION['last_attemptted_at'] - (int)$_SESSION['first_attemptted_at'] >= 180 && $result['count'] >= 3)
{
  $stmt5 = $pdo->prepare('DELETE FROM login_attempts WHERE username = :username AND count = 3 LIMIT 1'); // 要修正
  $stmt5->bindvalue(':username', $username, PDO::PARAM_STR);
  $stmt5->execute();
  session_unset();
  session_destroy();
  $limitLoginMessage = '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。';
}

// CSRF対策
$inputToken = filter_input(INPUT_POST, 'token');
$sessionToken = $_SESSION['token'] ?? '';
if ($sessionToken !== $inputToken && filter_input(INPUT_SERVER,'REQUEST_METHOD') !== 'GET' && $limitLoginMessage !== '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。')
{
  $errorMessage = '不正なリクエストです';
  http_response_code(400);
}
else
{
  $stmt = $pdo->prepare('SELECT * FROM login WHERE username = :username');
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->execute();

  // ユーザー名とパスワードのチェック
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($result !== false && password_verify($password, $result['password'])) // 検証
  {
    $_SESSION['username'] = $result['username'];

    // $stmt5 = $pdo->prepare('UPDATE login_attempts SET count = 0, last_attemptted_at = :last_attemptted_at WHERE username = :username');
    $stmt5 = $pdo->prepare('UPDATE login_attempts SET count = 0 WHERE username = :username');
    $stmt5->bindValue(':username', $username, PDO::PARAM_STR);
    // $stmt5->bindValue(':first_attemptted_at', $_SESSION['first_attemptted_at']);
    // $stmt5->bindValue(':last_attemptted_at', $_SESSION['last_attempted_at']);
    $stmt5->execute();

    http_response_code(200);
    header('Location: entries.php');
    exit;
  }
  elseif (filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'GET' && $limitLoginMessage !== '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。')
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
    <header>
      <h1 class="text-center py-5">
        エントリー一覧にログインする
      </h1>
    </header>
    
    <main>
      <?php if(!empty($limitLoginMessage)):?>
      <p class="alert alert-danger"><?php echo htmlspecialchars($limitLoginMessage); ?></p>
      <?php endif; ?>
      <?php if(isset($errorMessage)):?>
      <p class="alert alert-warning"><?php echo htmlspecialchars($errorMessage); ?></p>
      <?php endif; ?>
      <form action="" method="post">
        <label for="username" class="fw-bold">ユーザー名</label>
        <input type="text" name="username" id="username" class="form-control my-2" required value="<?php echo htmlspecialchars($username); ?>">
        <label for="password" class="fw-bold">パスワード</label>
        <input type="password" name="password" id="password" class="form-control my-2" required value="">
        <input type="hidden" name="attempts" value="<?php if (isset($attempts)){echo htmlspecialchars($attempts);}?>"> <!-- テスト -->
        <input type="hidden" name="token" value="<?php if (isset($_SESSION['token'])) {echo htmlspecialchars($_SESSION['token']);} ?>">
        <input type="submit" name="submit" class="btn btn-success btn-lg px-5 my-2" value="ログイン">
      </form>
    </main>

    <footer class="text-center fixed-bottom">
      &copy; 2023 Shimonig
    </footer>
  </div>
</body>
</html>
