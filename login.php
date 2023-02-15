<?php
session_start();

// DBに接続
$dbUser = 'yamadasan';
$dbPass = '1q2w3e4r5t';
$dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
$pdo = new PDO($dsn, $dbUser, $dbPass);

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');

// ログイン試行回数の制限

// ログイン制限の条件設定
$stmt4 = $pdo->prepare('SELECT count, first_attemptted_at, last_attemptted_at FROM login_attempts WHERE username = :username');
$stmt4->bindValue(':username', $username, PDO::PARAM_STR);
$stmt4->execute();
$loginAttemps = $stmt4->fetchAll();
$firstAttempttedAt = new DateTime($loginAttemps[0]['first_attemptted_at']);
$lastAttempttedAt = new DateTime($loginAttemps[0]['last_attemptted_at']);
$interval = $lastAttempttedAt->diff($firstAttempttedAt); // 日時の差分
$diffAttemptted = $interval->s + $interval->i * 60 + $interval->h * 60 * 60 + $interval->d * 24 * 60 * 60; // 秒・分・時間・日
$loginAttempttedCount = $loginAttemps[0]['count'];
$limitLogin = $diffAttemptted <= 180 && $loginAttempttedCount >= 3; // ログイン制限の条件

// ログイン試行回数と制限時間
$attempts = 0;
$blockedUntil = 0;
if (isset($_SESSION['attempts']))
{
  $attempts = (int)$_SESSION['attempts'];
}

if (isset($_SESSION['blockedUntil'])) 
{
  $blockedUntil = (int)$_SESSION['blockedUntil'];
}

if (isset($_SESSION['first_attemptted_at']) && isset($_SESSION['last_attemptted_at'])) 
{
  if ($limitLogin)
  {
    $_SESSION['blockedUntil'] = date('YmdHis') + 180;
  }
}

// ログイン試行回数とログイン試行日時
if (filter_input(INPUT_POST, 'submit')) 
{
  if (date('YmdHis') >= $blockedUntil) 
  {
    $attempts++;
    if ($attempts === 1) 
    {
      $_SESSION['first_attemptted_at'] = date('YmdHis');
    }
      $_SESSION['last_attemptted_at'] = date('YmdHis');
  }
}
$_SESSION['attempts'] = $attempts;

// データのリセット、アップデート
if ($_SESSION['attempts'] > 3) // ログイン試行回数の上限
{
  $_SESSION['attempts'] = 0;
}
$stmt2 = $pdo->prepare('SELECT * FROM login WHERE username = :username');
$stmt2->bindValue(':username', $username, PDO::PARAM_STR);
$stmt2->execute();

$limitLoginMessage = '';
// ログイン試行
if ($stmt2->fetchAll() !== false)
{
  // ログイン成功時
  if (password_verify($password, $stmt2->fetchAll()[0]['password']))
  {
    $_SESSION['attempts'] = 0; // ログイン試行回数をリセット
    $stmt3 = $pdo->prepare('UPDATE login_attempts SET count = :count WHERE username = :username');
    $stmt3->bindValue(':count', $_SESSION['attempts'], PDO::PARAM_STR);
    $stmt3->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt3->execute();
  }
  
  // ログイン失敗時（ログイン試行回数の上限達成）
  if ($limitLogin)
  {
    $limitLoginMessage = '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。';
    // $_SESSION['attempts'] = 0;
    $stmt3 = $pdo->prepare('UPDATE login_attempts SET count = 0, first_attemptted_at = :first_attemptted_at, last_attemptted_at = :last_attemptted_at WHERE username = :username');
    $stmt3->bindValue(':first_attemptted_at', $_SESSION['first_attemptted_at'], PDO::PARAM_INT);
    $stmt3->bindValue(':last_attemptted_at', $_SESSION['last_attemptted_at'], PDO::PARAM_INT);
    $stmt3->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt3->execute();
  }

  // ログイン失敗時（ログイン試行回数の上限未達）
  $stmt3 = $pdo->prepare('UPDATE login_attempts SET count = :count, first_attemptted_at = :first_attemptted_at, last_attemptted_at = :last_attemptted_at WHERE username = :username');
  $stmt3->bindValue(':count', $_SESSION['attempts'], PDO::PARAM_INT);
  $stmt3->bindValue(':first_attemptted_at', $_SESSION['first_attemptted_at'], PDO::PARAM_INT);
  $stmt3->bindValue(':last_attemptted_at', $_SESSION['last_attemptted_at'], PDO::PARAM_INT);
  $stmt3->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt3->execute();
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
  // if ($limitLogin)
  // {
    // $limitLoginMessage = '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。';
    if ($result !== false && password_verify($password, $result['password']))
    {
      $_SESSION['username'] = $result['username'];

      http_response_code(200);
      header('Location: entries.php');
      exit;
    }
    elseif (filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'GET' && $limitLoginMessage !== '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。')
    {
      $errorMessage = '正しいユーザー名またはパスワードを入力してください';
      http_response_code(400);
    }
  // }
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
