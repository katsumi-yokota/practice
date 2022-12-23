<?php
// DSN（データソースネーム）
$dsn = 'mysql:host=localhost;dbname=form-db;charaset=utf8';
// ユーザー名。初期設定はrootユーザー（全権限を持つ）が用意されているが、権限を何人かのユーザーに分けるケースが多い
$user = 'yamadasan';
// パスワード
$pass ='1q2w3e4r5t';
// オプション...なくてもいい。今回はなし

// try～catchでDBのエラーをチェックする
try 
{
  $dbh = new PDO($dsn, $user, $pass, 
  [ // エラーモードを例外で出力する
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
  echo '接続が成功しました';
  // 接続の終了はnullで表現できる
  $dbh = null;
}
// エラーの出力
catch(PDOException $e) 
{ 
  // $e->getMessage()を忘れると出力されない
  echo '接続が失敗しました' . $e->getMessage();
  // 失敗した場合はexitで処理を終わらせる
  exit();
};

?>
