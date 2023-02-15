<?php
// ログイン制限のテスト用
// DBに接続
$dbUser = 'yamadasan';
$dbPass = '1q2w3e4r5t';
$dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
$pdo = new PDO($dsn, $dbUser, $dbPass);

session_start();

// DBからデータを取得
$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');
$stmt = $pdo->prepare('SELECT * FROM tlogin_attempts WHERE username = :username');
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$loginAttempts = $stmt->fetch(PDO::FETCH_ASSOC);

// ログイン試行回数を初期化
$attempts = 1;
if (empty($loginAttempts)) 
{
  $stmt = $pdo->prepare('INSERT IGNORE INTO tlogin_attempts (username, attempts, last_attempted_at) VALUES (:username, 0, NOW())');
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->execute();
} 
else 
{
  // ログイン試行回数を更新
  $stmt = $pdo->prepare('UPDATE tlogin_attempts SET attempts = attempts + 1, last_attempted_at = NOW() WHERE username = :username');
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->execute();
  $attempts = $loginAttempts['attempts'] + 1;
}

// ログイン制限
if ($attempts >= 3 && time() - strtotime($loginAttempts['last_attempted_at']) <= 180) 
{
  $errorMessage = '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。';
} 
else 
{
  // ユーザー名とパスワードのチェック
  $stmt = $pdo->prepare('SELECT * FROM login WHERE username = :username AND password = :password');
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->bindValue(':password', $password, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($result !== false)
  {
    // ログイン成功
    $_SESSION['username'] = $result['username'];
    // ログイン試行回数を初期化
    $stmt = $pdo->prepare('UPDATE tlogin_attempts SET attempts = 0, last_attempted_at = NOW() WHERE username = :username');
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    header('Location: entries.php');
    exit;
  }
  else
  {
    // ログイン失敗
    $errorMessage = '正しいユーザー名またはパスワードを入力してください';

    // 最後のログイン試行から24時間以上経過している場合、ログイン試行回数をリセット
    if (time() - strtotime($loginAttempts['last_attempted_at']) >= 86400) 
		{
      $stmt = $pdo->prepare('UPDATE tlogin_attempts SET attempts = 0, last_attempted_at = NOW() WHERE username = :username');
      $stmt->bindValue(':username', $username, PDO::PARAM_STR);
      $stmt->execute();
    }
  }
}

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
      <?php if(!empty($errorMessage) && filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST'):?>
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
