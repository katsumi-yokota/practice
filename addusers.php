<?php
// Basic認証
$basicUser = 'userrrr';
$basicPass = 'pass';

if(isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER']==$basicUser && $_SERVER['PHP_AUTH_PW']==$basicPass))
{
  $successLogin = ' さんがログイン中です';
} 
else
{
  header('WWW-Authenticate: Basic realm="Basic"');
  header('HTTP/1.0 401 Unauthorized - basic');
  echo "<p>Unauthorized</p>";
  exit();
}

// DBに保存
$dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
$dbUser = 'yamadasan';
$dbPass = '1q2w3e4r5t';
$pdo = new PDO($dsn, $dbUser, $dbPass);

$username = filter_input(INPUT_POST, 'username');
$password = password_hash(filter_input(INPUT_POST, 'password'), PASSWORD_DEFAULT);

if (empty($username) || empty($password))
{
  $message = 'ユーザー名とパスワードを両方入力してください';
  http_response_code(400);
}
else
{
  $message = 'ユーザー名とパスワードの追加に成功しました';
  $sql = "INSERT INTO login (username, password) VALUES (:username, :password)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':username', $username, PDO::PARAM_STR);
  $stmt->bindValue(':password', $password, PDO::PARAM_STR);
  $stmt->execute();
  http_response_code(201);
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
  <header>
    <p><?php if (isset($message)) {echo $message;} ?></p>
    <p><?php echo $_SERVER['PHP_AUTH_USER'] . $successLogin; ?></p>
    <h1>ユーザーを追加する</h1>
  </header>

  <main>
    <form action="" method="post">
      <label for="">ユーザー名</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
      <label for="">パスワード</label>
      <input type="password" name="password">
      <input type="submit">
    </form>
  </main>
</body>
</html>
