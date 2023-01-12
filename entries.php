<?php
$pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t'); 

// エントリーをデフォルトで５件、指定された場合は１０件、１５件、２０件表示
$perPage = 5; // 1ページに何件エントリーを表示させるか、のデフォルト
if(isset($_GET['limit']))
{
  $perPage = (int)$_GET['limit'];
}

// 0以下の場合は5件表示
if ($perPage <= 0)
{
  $perPage = 5;
}

$total = $pdo->query('SELECT COUNT(*) FROM entries')->fetchColumn();
$totalPages = ceil($total / $perPage); 

// 存在するページが入力された場合はそのページに飛ばし、そうでなければ1ページ目に飛ばす。三項演算子を使うことも可能
$page = 1; // 表示させるページのデフォルト
if(isset($_GET['page']) && preg_match('/^[1-9][0-9]*$/', $_GET['page']) && $_GET['page'] <= $totalPages) 
{
  $page = (int)$_GET['page'];
}


$sort = 'asc'; // 昇順降順のデフォルト。変な値を入れたときでも正常に見える
if(isset($_GET['direction']) && $_GET['direction'] === 'desc')
{
  $sort = 'desc';
}

// ソート可能なカラムのnameの配列をつくりin_arrayで配列にnameがあるかチェックしてある場合はそのnameを変数sortColumnに格納
$sortableColumns = ['id', 'name', 'email', 'gender', 'position', 'work', 'question'];
$sortColumn = 'id'; // 安全のために初期化
if(in_array($_GET['sort-column'], $sortableColumns, true)) // 第三引数はtrueにして厳密に比較
{
  $sortColumn = $_GET['sort-column'];
}

$offset = $perPage * ($page - 1);
$sql = 'SELECT * FROM entries ORDER BY '.$sortColumn.' '.$sort.' LIMIT :offset, :limit'; // '.$sort.'で変数sortをSQL文にねじ込む
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- <th>#
      <button type="submit" form="名前の並び替え">並び替え</button>
    </th> -->
    <th class="bg-info">#</th>
    <th class="bg-info">名前</th>
    <th class="bg-info">メールアドレス</th>
    <th class="bg-info">性別</th>
    <th class="bg-info">希望ポジション</th>
    <th class="bg-info">前職</th>
    <th class="bg-info">質問</th>
  </tr>

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

<nav aria-label="Pagination" class="my-5">
  <ul class="pagination pagination-lg justify-content-center">
    <?php if($page > 1) : ?>
    <!-- クエリパラメータを変更して表示件数や昇順降順を変えても遷移時、遷移後に正常に動作させる -->
    <li class="page-item"><a class="page-link" href="?limit=<?php echo $perPage; ?>&page=<?php echo $page - 1; ?>&direction=<?php echo $sort; ?>">前へ</a></li>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
    <li class="page-item"><a class="page-link" href="?limit=<?php echo $perPage; ?>&page=<?php echo $i; ?>&direction=<?php echo $sort; ?>"><?php echo $i; ?></a></li> 
    <?php endfor; ?>
    <?php if($page < $totalPages) : ?>
    <li class="page-item"><a class="page-link" href="?limit=<?php echo $perPage; ?>&page=<?php echo $page + 1; ?>&direction=<?php echo $sort; ?>">次へ</a></li>
    <?php endif; ?>
  </ul>
</nav>

<form action="" method="GET">
<!-- divタグでくくって「ひとつブロック」とする。form内でdivタグを使うことは可能。なおdivとはdivision（分割）の意 -->
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-2 mb-2">
        <label for="表示件数" class="form-label">表示件数:</label>
        <select class="form-select" name="limit" id="表示件数">
          <!-- selectedを使って指定した件数を固定 -->
          <option value="5" <?php if ($perPage === 5) : ?>selected<?php endif; ?>>5件</option>
          <option value="10" <?php if ($perPage === 10) : ?>selected<?php endif; ?>>10件</option>
          <option value="15" <?php if ($perPage === 15) : ?>selected<?php endif; ?>>15件</option>
          <option value="20" <?php if ($perPage === 20) : ?>selected<?php endif; ?>>20件</option>
        </select>
      </div>
    </div>
    <input type="hidden" name="page" value="<?php echo $page; ?>"> 

      <!-- 昇順降順のソート(並び替え)に関する情報をインプットする -->
    <div class="row justify-content-center">
      <div class="col-lg-2 mb-1">
        <label for="昇順降順" class="form-label">昇順降順:</label>
        <select class="form-select" name="sort-column" id="昇順降順">
          <option value="id" selected>id</option>
          <option value="name" selected>名前</option>
          <option value="email" selected>メールアドレス</option>
          <option value="gender" selected>性別</option>
          <option value="position" selected>希望のポジション</option>
          <option value="work" selected>前職</option>
          <option value="question" selected>質問</option>
        <input type="radio" class="form-check-input" name="direction" value="asc" <?php if(isset($sort) && $sort === 'asc') {echo 'checked';}?>>昇順
        <input type="radio" class="form-check-input" name="direction" value="desc" <?php if(isset($sort) && $sort === 'desc') {echo 'checked';}?>>降順
      </div>
    </div>
      
    <div class="row justify-content-center">
      <div class="col-lg-2 text-center">
        <input class="form-control btn-info" type="submit">
      </div>
    </div>
  </div>
</form>
</body>
</html>
