<?php
// エントリーをデフォルトで５件、指定された場合は１０件、１５件、２０件表示
$enrtries_per_page = 5;
if(isset($_GET['entries-shown']))
{
  $enrtries_per_page = (int)$_GET['entries-shown'];
}

var_dump($enrtries_per_page);

// 0以下の場合は5件表示
if ($enrtries_per_page <= 0)
{
  $enrtries_per_page = 5;
}

$pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t'); 

$total = $pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
$totalPages = ceil($total / $enrtries_per_page); 

// 存在するページが入力された場合はそのページに飛ばし、そうでなければ1ページ目に飛ばす
if 
(
  preg_match('/^[1-9][0-9]*$/', $_GET['page']) and
  $_GET['page'] <= $totalPages
) 
{
  $page = (int)$_GET['page'];
}
else
{
  $page = 1;
}
var_dump($page);

$offset = $enrtries_per_page * ($page - 1);
var_dump($offset);
$sql = 'SELECT * FROM entries LIMIT :offset, :limit';

// 1. 準備、2. 紐付け、 3. 実行、 4. 取得
$stmt = $pdo->prepare($sql); // 1. prepareメソッドを使ってSQL文を実行する「準備」
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT); // 2. bindValueメソッドを使ってプレースホルダに値を「紐付け」
$stmt->bindValue(':limit', $enrtries_per_page, PDO::PARAM_INT);
$stmt->execute(); // 3. executeメソッドを使って「実行」
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC); // 4. fetch(fetchAll)メソッドでデータを「取得」
?>

<!DOCTYPE php>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="test-paging-style.css">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>エントリー一覧</title>
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

<!-- // foreachで配列の中身を一行ずつ出力 -->
<?php foreach ($entries as $entry): ?>
  <tr>
    <td><?php echo $entry['id']; ?></td>
    <td><?php echo $entry['name']; ?></td>
    <td><?php echo $entry['email']; ?></td>
    <td><?php echo $entry['gender']; ?></td>
    <td><?php echo $entry['position']; ?></td>
    <td><?php echo $entry['work']; ?></td>
    <td><?php echo htmlspecialchars($entry['question'], ENT_QUOTES, 'UTF-8'); ?></td>
  </tr>
<?php endforeach; ?>
</table>

<!-- Bootstrapでページネーションを実装 -->
<nav aria-label="Pagination" class="my-5">
  <ul class="pagination pagination-lg justify-content-center">
    <?php if($page > 1) : ?>
    <!-- クエリパラメータを変更して表示件数を変えても移動時に正常に動作させる -->
    <li class="page-item"><a class="page-link" href="?entries-shown=<?php echo $enrtries_per_page; ?>&page=<?php echo $page - 1; ?>&submit=<?php echo '変更する'; ?>">前へ</a></li>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
    <li class="page-item"><a class="page-link" href="?entries-shown=<?php echo $enrtries_per_page; ?>&page=<?php echo $i; ?>&submit=<?php echo '変更する'; ?>"><?php echo $i; ?></a></li> 
    <?php endfor; ?>
    <?php if($page < $totalPages) : ?>
    <li class="page-item"><a class="page-link" href="?entries-shown=<?php echo $enrtries_per_page; ?>&page=<?php echo $page + 1; ?>&submit=<?php echo '変更する'; ?>">次へ</a></li>
    <?php endif; ?>
  </ul>
</nav>

<form method="GET" action="">
  <label for="表示件数">表示件数:</label>
  <select name="entries-shown" id="表示件数">
    <!-- selectedを使って指定した件数を固定 -->
    <option value="5" <?php if ($enrtries_per_page === 5) : ?>selected<?php endif; ?>>5件</option>
    <option value="10" <?php if ($enrtries_per_page === 10) : ?>selected<?php endif; ?>>10件</option>
    <option value="15" <?php if ($enrtries_per_page === 15) : ?>selected<?php endif; ?>>15件</option>
    <option value="20" <?php if ($enrtries_per_page === 20) : ?>selected<?php endif; ?>>20件</option>
  </select>
  <input type="hidden" name="page" value="<?php 'page'; echo $page; ?>">
  <input type="submit" name="submit" value="変更する" class="btn-info px-1" />

</form>

</body>
</html>
