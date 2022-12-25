<?php
//try～catchでDBのエラーチェック
try {
  //DBの名前、ユーザー名、パスワードを変数に入れる
  $dsn = 'mysql:dbname=form-db;host=localhost;charset=utf8'; //DSNはデータソースネーム。utf8:1～3バイトまで対応、utf8mb4:1～4バイトまで対応
  $user = 'yamadasan'; //ユーザー名。初期設定ではrootユーザー（全権限を持つ）が用意されているが、新しいユーザーをつくるのが望ましい。権限は複数人のユーザーで分けるのが一般的
  $password = '1q2w3e4r5t';
 
  $PDO = new PDO($dsn, $user, $password); //PDOでデータベースに接続
  $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDOのエラーレポートを表示
 
  //form2.phpの値を取得
  //AI（オートインクリメント）を設定しているのでidについては書かない
  $name = $_POST['name'];
  $email = $_POST['email'];
  $gender = $_POST['gender'];
  $position = $_POST['position'];
  $work = $_POST['work'];
  $question = $_POST['question'];
 
  $sql = "INSERT INTO entries (name, email, gender, position, work, question) VALUES (:name, :email, :gender, :position, :work, :question)"; //テーブルに登録するINSERT INTO文を変数sqlに入れる。VALUESはプレースホルダーで空の値を入れる、その際には:（コロン）を使う
  $stmt = $PDO->prepare($sql); //プリペアードステートメント
  $params = array(':name' => $name, ':email' => $email, ':gender' => $gender, ':position' => $position, ':work' => $work, 'question' => $question); //変数paramsをつくり挿入する値を配列に入れる
  $stmt->execute($params); //execute関数を使ってSQLを実行する
 
  //エントリー内容の確認とエントリー完了or未完了のメッセージ
  echo "<p>名前: " . $name . "</p>";
  echo "<p>メールアドレス: " . $email . "</p>";
  echo "<p>性別: " . $gender . "</p>";
  echo "<p>ポジション: " . $position . "</p>";
  echo "<p>前職: " . $work . "</p>";
  echo "<p>質問: " . $question . "</p>";
  echo '<p>上記の内容でエントリーが完了しました！お疲れ様でした。担当者がご連絡します。</p>';
} catch (PDOException $e) {
  exit('エントリーは完了していません！' . $e->getMessage());
}
?>
