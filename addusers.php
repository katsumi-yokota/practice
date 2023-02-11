<?php
// Basic認証
$basicUser = 'userrrr';
$basicPass = 'passs';

if(isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER']==$basicUser && $_SERVER['PHP_AUTH_PW']==$basicPass))
{
  $authenticationMessage = ' さんがログイン中です。';
} 
else
{
  header('WWW-Authenticate: Basic realm="Basic"');
  header('HTTP/1.0 401 Unauthorized - basic');
  $authenticationMessage = '認証されていません。';
  exit();
}

// DBに保存
$dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
$dbUser = 'yamadasan';
$dbPass = '1q2w3e4r5t';
$pdo = new PDO($dsn, $dbUser, $dbPass);

// 入力チェック
$username = filter_input(INPUT_POST, 'username');
$password = password_hash(filter_input(INPUT_POST, 'password'), PASSWORD_DEFAULT);
if (empty($username) || empty($password))
{
  $addUsersMessage = 'ユーザー名とパスワードを両方入力してください。';
  http_response_code(400);
}
else
{
  // 重複防止
  $avoidDuplicate = 'SELECT username FROM login WHERE username = :username';
  $stmt = $pdo->prepare($avoidDuplicate);
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->execute();
  if (count($stmt->fetchAll()) > 0)
  {
    $addUsersMessage = 'ユーザー名が重複しています。別のユーザー名を入力してください。';
    http_response_code(400);
  }
  else
  {
    $addUsersMessage = 'ユーザー名とパスワードの追加に成功しました';
    $sql = 'INSERT INTO login (username, password) VALUES (:username, :password)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    http_response_code(201);
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
  <title>ユーザー追加</title>
</head>
<body>
  <div class="container">
    <header>
      <p class="py-2"><?php echo htmlspecialchars($_SERVER['PHP_AUTH_USER']) . $authenticationMessage; ?></p>
      <h1 class="text-center my-5">新規ユーザーを追加する</h1>
      <?php if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST' && !empty($addUsersMessage)): ?>
      <p class="alert alert-warning"><?php echo $addUsersMessage; ?></p>
      <?php endif; ?>
    </header>

    <main>
      <p class="text-center large">新規ユーザーを追加します。<span class="fw-bold">ユーザー名</span>と<span class="fw-bold">パスワード</span>を入力して「追加する」ボタンを押してください。</p>
      <form action="" method="post">
        <label for="username">ユーザー名</label>
        <input type="text" class="form-control mb-2" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>">
        <label for="password">パスワード</label>
        <input type="password" class="form-control mb-2" name="password" id="password">
        <input type="submit" class="btn btn-success px-5 mt-2" value="追加する">
      </form>
    </main>
  </div>
</body>
</html>
