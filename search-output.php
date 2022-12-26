<?php
  $pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t');
  $sql = 'SELECT * FROM entries';
  $entries = $pdo->query($sql);
  // $stmt = $pdo->prepare('SELECT * FROM entries where name=?'); //プリペアードステートメント
  // $stmt->execute([$_POST['keyword']]); //「?」に当たる部分を配列にして渡す
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
<!-- table-borderedで罫線を引き、table-stripedで交互に配色 -->
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
  <!-- // foreachで配列の中身を一行ずつ出力 -->
<?php foreach ($entries as $entry): ?>
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
