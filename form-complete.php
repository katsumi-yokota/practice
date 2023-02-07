<?php
session_start();
$_SESSION = array();

//バリデーション
//必須
$name = '';
$email = '';
$positions = '';
$dangerName = filter_input(INPUT_POST, 'name');
$dangerEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$dangerPositions = filter_input(INPUT_POST, 'positions', FILTER_DEFAULT, ['flags' => FILTER_REQUIRE_ARRAY]) ?? [];

// 保存用
if (isset($dangerName) && !empty($dangerName) && mb_strlen($dangerName) <= 40)
{
  $name = $dangerName;
  $_SESSION['name'] = $dangerName;
}
else
{
  $_SESSION['messageForName'] = '正しいお名前を入力してください';
}
// 全体の否定のテスト
// if !(isset($dangerName) && !empty($dangerName) && mb_strlen($dangerName) <= 40)
// {
//   $_SESSION['messageForName'] = '正しいお名前を入力してください';
// }
if (isset($dangerEmail) && !empty($dangerEmail) && mb_strlen($dangerEmail) <= 254)
{
  $email = $dangerEmail;
  $_SESSION['email'] = $dangerEmail;
}
else
{
  $_SESSION['messageForEmail'] = '正しいメールアドレスを入力してください';
}
if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST' && empty($dangerPositions)) //フォームが送信されたときのみ処理
{
  $_SESSION['messageForPositions'] = 'ご希望のポジションを入力してください';
}
// 保存用
if (in_array('SE', $dangerPositions) || in_array('プログラマー', $dangerPositions) || in_array('インフラエンジニア', $dangerPositions)) //要修正
{
  $positions = implode(', ',$dangerPositions);
  $_SESSION['positions'] = $dangerPositions;
}

//非必須
$gender = '';
$work = '';
$question = '';
$annualIncome = '';
$dangerGender = filter_input(INPUT_POST, 'gender');
$dangerWork = filter_input(INPUT_POST, 'work');
$dangerQuestion = filter_input(INPUT_POST, 'question');
$dangerAnnualIncome = filter_input(INPUT_POST, 'annual_income');
if (preg_match('/^(男性|女性|その他|未回答)$/', $dangerGender)) 
{
     $gender = $dangerGender;
     $_SESSION['gender'] = $dangerGender;
}
if (mb_strlen($dangerWork) <= 40)
{
     $work = $dangerWork;
     $_SESSION['work'] = $dangerWork;
}
if (mb_strlen($dangerQuestion) <= 100)
{
     $question = $dangerQuestion;
     $_SESSION['question'] = $dangerQuestion;
}
if (preg_match('/^[0-9]+$/', $dangerAnnualIncome))
{
     $annualIncome = $dangerAnnualIncome;
     $_SESSION['annual_income'] = $dangerAnnualIncome;
}

if (empty($_SESSION['messageForName']) && empty($_SESSION['messageForEmail']) && empty($_SESSION['messageForPositions']))
{
  try 
  {
    $dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8';
    $user = 'yamadasan';
    $password = '1q2w3e4r5t';
  
    $PDO = new PDO($dsn, $user, $password);
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDOのエラーレポートを表示

    //$sqlに格納
    $sql = "INSERT INTO entries (name, email, gender, position, work, question, annual_income) VALUES (:name, :email, :gender, :position, :work, :question, :annual_income)";
    $stmt = $PDO->prepare($sql);
    $params = array(':name' => $name, ':email' => $email, ':gender' => $gender, ':position' => $positions, ':work' => $work, ':question' => $question, ':annual_income' => $annualIncome); // 簡易的にバインド
    
    $stmt->execute($params);
  
  } 
  catch (PDOException $e) 
  {
    exit('エントリーは完了していません！' . $e->getMessage());
  }
  
}
else
{
  // header("Location: form.php", true, 400); // 保存用
  header("Location: form.php");
  exit();
}
// $_SESSION = array();
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

      <p class="text-center fw-bold my-3">
        <?php echo htmlspecialchars($name). ' 様'; ?>
      </p>

      <p class="text-center my-3">
        この度は、Shimoningへの求人採用にエントリーしていただき、誠にありがとうございます。<br>
        担当者よりご連絡をさせていただきますので、今しばらくお待ちくださいますよう、よろしくお願い申し上げます。
      </p>

      <p class="text-center small">
        ※エントリー内容につきましては、下記の通りです。
      </p>

      <ul class="list-group list-group-flush border border-success">
        <li class="list-group-item"><?php echo 'お名前: ' . htmlspecialchars($name) . ' 様'; ?></li>
        <li class="list-group-item"><?php echo 'メールアドレス: ' . htmlspecialchars($email); ?></li>
        <li class="list-group-item"><?php echo '性別: ' . htmlspecialchars($gender); ?></li>
        <li class="list-group-item"><?php echo 'ご希望のポジション: ' . htmlspecialchars($positions); ?></li>
        <li class="list-group-item"><?php echo '前職: ' . htmlspecialchars($work); ?></li>
        <li class="list-group-item"><?php echo 'ご質問: ' . htmlspecialchars($question); ?></li>
        <li class="list-group-item"><?php echo 'ご希望の年収: ' . htmlspecialchars($annualIncome) . ' 万円'; ?></li>
      </ul>
    </main>

  </div>
</body>
</html>
