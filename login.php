<?php
session_start();

// DBに接続
require_once('pdo.php');

$username = filter_input(INPUT_POST, 'username');
$password = filter_input(INPUT_POST, 'password');

/*
ログイン試行回数の制限
*/
require_once('class-login.php');

$login = new Login($pdo);
$loginAttemps = $login->limitLogin($username);
$limitLogin = '';
if (isset($loginAttemps[0]['first_attemptted_at']) && isset($loginAttemps[0]['last_attemptted_at']))
{
  $limitLogin = strtotime($loginAttemps[0]['last_attemptted_at']) - strtotime($loginAttemps[0]['first_attemptted_at']) <= 180 && $loginAttemps[0]['count'] >= 3; // 制限条件
}

// ログイン試行日時とログイン試行回数
$storeFirstAttempttedAt = 0;
$attempts = filter_input(INPUT_POST, 'attempts');
if (filter_input(INPUT_POST, 'submit')) 
{
  $attempts++;
  if ($attempts === 1 && isset($storeFirstAttempttedAt))
  {
    $storeFirstAttempttedAt = date('YmdHis');
  }
}

// 最後のログイン試行から一定時間後、ログイン試行回数をリセット
if (isset($loginAttemps[0]))
{
  $attempts = time() - strtotime($loginAttemps[0]['last_attemptted_at']) >= 180 ?  0 : $attempts;
}

// ログイン試行
$isNameExistOnLogin = $login->searchByUsername($username);
$limitLoginMessage = '';
var_dump($isNameExistOnLogin);
// テーブル「login」にusernameがある
if (is_array($isNameExistOnLogin) && count($isNameExistOnLogin) > 0)
// if ($isNameExistOnLogin !== false)
{
  echo 'ある!(確認用)';
  // ログイン制限がかかっている
  if ($limitLogin)
  {
    echo 'ログイン制限がかかっている（確認用）';
    $limitLoginMessage = '誤った入力が繰り返しされたため、ログインを制限しました。しばらく時間をおいてから再度お試しください。';
    $storeFirstAttempttedAt = date('YmdHis');
  }
  // ログイン制限がかかっていない、かつ、パスワードが存在する（合っている）→ログイン成功
  elseif ($isNameExistOnLogin['password'])
  {
    $attempts = 0;
    $storeFirstAttempttedAt = date('YmdHis');
  }
  // ログイン制限がかかっていない、かつ、パスワードが存在しない（合っていない）
  else
  {
    echo 'パスワードが合っていない、かつ、ログイン制限がかかっていない（確認用）';
  }
    $stmt3 = $pdo->prepare('UPDATE login_attempts SET count = :count, first_attemptted_at = :first_attemptted_at, last_attemptted_at = NOW() WHERE username = :username');
    $stmt3->bindValue(':count', $attempts, PDO::PARAM_INT);
    $stmt3->bindValue(':first_attemptted_at', $storeFirstAttempttedAt, PDO::PARAM_INT);
    $stmt3->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt3->execute();
}

// テーブル「login_attempts」にusernameがなければ新規登録
$isNameExistOnLoginAttempts = $login->insertNewRecords($username);
if ($isNameExistOnLoginAttempts === false)
{
  echo 'テーブル「login_attempts」にusernameがないため新規登録（確認用）';
  $stmt3 = $pdo->prepare('INSERT INTO login_attempts (username, count, first_attemptted_at, last_attemptted_at) VALUE (:username, 1, NOW(), NOW())');
  $stmt3 = $pdo->prepare('INSERT IGNORE INTO login (username) VALUE (:username)');
  $stmt3->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt3->execute();
}

/*
CSRF対策
*/
require_once('csrf.php');

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
