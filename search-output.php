<!DOCTYPE php>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <title>検索機能（出力）</title>
</head>
<body>
<table>
  <tr>
    <th>番号</th>
    <th>名前</th>
    <th>希望ポジション</th>
    <th>質問</th>
  </tr>
  <?php
  $PDO = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t');
  $sql = $PDO->prepare('SELECT * FROM entries where name=?'); //プリペアードステートメント
  $sql->execute([$_REQUEST['keyword']]); //「?」に当たる部分を配列にして渡す
  foreach ($sql as $row)
  {
    echo '<tr>';
    echo '<td>', $row['id'], '</td>';
    echo '<td>', $row['name'], '</td>';
    echo '<td>', $row['position'], '</td>';
    echo '<td>', $row['question'], '</td>';
    echo '</tr>';
    // 「〇人ヒットしました」と表示させたい
  }
  ?>
</table>
</body>
</html>
