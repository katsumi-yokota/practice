<?php
try {
  $dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
  $user = 'yamadasan';
  $password = '1q2w3e4r5t';
 
  $PDO = new PDO($dsn, $user, $password); //データベースに接続
  $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDOのエラーレポートを表示
 
  //AI（オートインクリメント）を設定しているのでidは書かない
  $name = htmlspecialchars(filter_input(INPUT_POST, 'name'));
  $email = htmlspecialchars(filter_input(INPUT_POST, 'email'));
  $gender = htmlspecialchars(filter_input(INPUT_POST, 'gender'));
  $position = htmlspecialchars(filter_input(INPUT_POST, 'position'));
  $work = htmlspecialchars(filter_input(INPUT_POST, 'work'));
  $question = htmlspecialchars(filter_input(INPUT_POST, 'question'));
  $annual_income = htmlspecialchars(filter_input(INPUT_POST, 'annual_income'));
 
  $sql = "INSERT INTO entries (name, email, gender, position, work, question, annual_income) VALUES (:name, :email, :gender, :position, :work, :question, :annual_income)";
  $stmt = $PDO->prepare($sql);
  $params = array(':name' => $name, ':email' => $email, ':gender' => $gender, ':position' => $position, ':work' => $work, 'question' => $question, 'annual_income' => $annual_income); // 簡易的にバインド
  $stmt->execute($params);
 
} catch (PDOException $e) {
  exit('エントリーは完了していません！' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>ご応募ありがとうございます</title>
</head>

<body>
  <div class="container">
    <header class="text-center text-success mt-3">
      <h1>ENTRY</h1>
    </header>

    <div class="text-center my-3">
      <svg xmlns="http://www.w3.org/2000/svg" width="77" height="77" fill="currentColor" class="bi bi-check2-circle" viewBox="0 0 16 16">
      <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
      <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
      </svg>      
    </div>

    <main>
      <h2 class="text-center text-success">
        エントリー完了
      </h2>

      <p class="text-center my-3">
        この度はShimoningへの求人採用にエントリーしていただき、誠にありがとうございます。<br>
        担当者よりご連絡をさせていただきますので、今しばらくお待ちくださいますよう、よろしくお願い申し上げます。
      </p>

      <p class="text-center">
        なお、エントリー内容につきましては、下記の通りです。
      </p>

      <ul class="list-group list-group-flush border border-success">
        <li class="list-group-item"><?php echo 'お名前: ' . $name . '様'; ?></li>
        <li class="list-group-item"><?php echo 'メールアドレス: ' . $email; ?></li>
        <li class="list-group-item"><?php echo '性別: ' . $gender; ?></li>
        <li class="list-group-item"><?php echo 'ポジション: ' . $position; ?></li>
        <li class="list-group-item"><?php echo '前職: ' . $work; ?></li>
        <li class="list-group-item"><?php echo '質問: ' . $question; ?></li>
        <li class="list-group-item"><?php echo '希望年収: ' . $annual_income . '万円'; ?></li>
      </ul>
    </main>

    <!-- <footer class="fixed-bottom text-center small">
      <p>&copy; 2023 Shimoning</p>
    </footer> -->
  </div>
</body>
</html>