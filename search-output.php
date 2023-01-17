<?php
$pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t');
// $sql = $pdo->prepare('SELECT * FROM entries WHERE name LIKE ?');
$keyword = $_GET['keyword'];
if (false !== strpos($keyword, '%')) // strposで文字列の有無を判定
{
  $keyword = str_replace('%', '\\%', $keyword); // エスケープをエスケープする // str_replaceで文字列の置換
}
if (false !== strpos($keyword, '_'))
{
  $keyword = str_replace('_', '\\_', $keyword);
}

$sql = $pdo->prepare('SELECT * FROM entries WHERE name LIKE ?');
$sql->execute(['%'.$keyword.'%']); // foreachで中身を取り出すために配列にする
?>

<!DOCTYPE php>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>検索機能（出力）</title>
</head>
  <body>
    <h1 class="text-center">エントリー一覧</h1>

      <table class="table table-bordered table-striped">
        <tr>
          <th class="bg-info">#</th>
          <th class="bg-info">名前</th>
          <th class="bg-info">メールアドレス</th>
          <th class="bg-info">性別</th>
          <th class="bg-info">希望ポジション</th>
          <th class="bg-info">前職</th>
          <th class="bg-info">質問</th>
        </tr>
        
        <?php foreach ($sql as $entry): ?>
        <tr>
          <td><?php echo $entry['id']; ?></td>
          <td><?php echo $entry['name']; ?></td>
          <td><?php echo $entry['email']; ?></td>
          <td><?php echo $entry['gender']; ?></td>
          <td><?php echo $entry['position']; ?></td>
          <td><?php echo $entry['work']; ?></td>
          <td><?php echo $entry['question']; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
  </body>
</html>
