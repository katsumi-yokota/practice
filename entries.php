<?php
$pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t'); 

// エントリーをデフォルトで５件、指定された場合は１０件、１５件、２０件表示
$perPage = 5; // 初期化。デフォルトで１ページ５件表示
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
$page = 1; // 初期化
if(isset($_GET['page']) && preg_match('/^[1-9][0-9]*$/', $_GET['page']) && $_GET['page'] <= $totalPages) 
{
  $page = (int)$_GET['page'];
}

$direction = 'asc'; // 初期化
if(isset($_GET['direction']) && $_GET['direction'] === 'desc')
{
  $direction = 'desc';
}

// ソート可能なカラムのnameの配列をつくりin_arrayで配列にnameがあるかチェックしてある場合はそのnameを変数sortColumnに格納
$sortableColumns = ['id', 'name', 'email', 'gender', 'position', 'work', 'question'];
$sortColumn = 'id'; // 初期化
// issetでNULLでないか、も同時に判定する
if(isset($_GET['sort-column']) && in_array($_GET['sort-column'], $sortableColumns, true)) // 第三引数はtrueにして厳密に比較
{
  $sortColumn = $_GET['sort-column'];
}
var_dump($sortColumn);

$offset = $perPage * ($page - 1);
$sql = 'SELECT * FROM entries ORDER BY '.$sortColumn.' '.$direction.' LIMIT :offset, :limit'; // '.$direction.'で変数sortをSQL文にねじ込む
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
    <header>
      <h1 class="text-center">エントリー一覧</h1>
        <table class="table table-bordered table-striped">
          <thead>
            <tr class="bg-info">
              <th>
                <!-- 各カラムを「ワンクリック目は昇順にソート」するコードを追加する -->
                <?php if ($sortColumn !== 'id'): ?> <!-- ワンクリック目はidを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=id&direction=asc">
                id
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'id' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=id&direction=asc">
                id <?php if ($sortColumn === 'id' && $direction === 'desc'): {echo '↑';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'id' && $direction === 'asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=id&direction=desc">
                id <?php if ($sortColumn === 'id' && $direction === 'asc'): {echo '↓';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
              <th>
                <?php if ($sortColumn !== 'name'): ?> <!-- ワンクリック目はnameを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=name&direction=asc">
                名前
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'name' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=name&direction=asc">
                名前 <?php if ($sortColumn === 'name' && $direction === 'desc'): {echo '↑';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'name' && $direction === 'asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=name&direction=desc">
                名前 <?php if ($sortColumn === 'name' && $direction === 'asc'): {echo '↓';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
              <th>
                <?php if ($sortColumn !== 'email'): ?> <!-- ワンクリック目はemailを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=email&direction=asc">
                メールアドレス
                </a>
                <?php endif; ?> 
                <?php if ($sortColumn === 'email' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=email&direction=asc">
                メールアドレス <?php if ($sortColumn === 'email' && $direction === 'desc'): {echo '↑';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'email' && $direction === 'asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>$page=<?php echo $page; ?>&sort-column=email&direction=desc">
                メールアドレス <?php if ($sortColumn == 'email' && $direction === 'asc'): {echo '↓';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
              <th>
                <?php if ($sortColumn !== 'gender'): ?> <!-- ワンクリック目はgenderを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=gender&direction=asc">
                性別
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'gender' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=gender&direction=asc">
                性別 <?php if ($sortColumn === 'gender' && $direction === 'desc'): {echo '↑';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'gender' && $direction === 'asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=gender&direction=desc">
                性別 <?php if ($sortColumn === 'gender' && $direction === 'asc'): {echo '↓';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
              <th>
                <?php if ($sortColumn !== 'position'): ?> <!-- ワンクリック目はpositionを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=position&direction=asc">
                希望のポジション
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'position' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=position&direction=asc">
                希望のポジション <?php if ($sortColumn === 'position' && $direction === 'desc'): {echo '↑';}?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'position' && $direction === 'asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=position&direction=desc">
                希望のポジション <?php if ($sortColumn === 'position' && $direction === 'asc'): {echo '↓';}?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
              <th>
                <?php if ($sortColumn !== 'work'): ?> <!-- ワンクリック目はworkを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=work&direction=asc">
                前職
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'work' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=work&direction=asc">
                前職 <?php if ($sortColumn === 'work' && $direction === 'desc'): {echo '↑';}?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'work' && $direction === 'asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=work&direction=desc">
                前職 <?php if ($sortColumn === 'work' && $direction === 'asc'): {echo '↓';}?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
              <th>
                <?php if ($sortColumn !== 'question'): ?> <!-- ワンクリック目はquestionを昇順にソート -->
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=question&direction=asc">
                質問
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'question' && $direction === 'desc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=question&direction=asc">
                質問 <?php if ($sortColumn === 'question' && $direction === 'desc'): {echo '↑';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php if ($sortColumn === 'question' && $direction ==='asc'): ?>
                <a href="entries.php?limit=<?php echo $perPage; ?>&page=<?php echo $page; ?>&sort-column=question&direction=desc">
                質問 <?php if ($sortColumn === 'question' && $direction === 'asc'): {echo '↓';} ?><?php endif; ?>
                </a>
                <?php endif; ?>
              </th>
            </tr>
          </thead>

          <?php foreach ($entries as $entry): ?>
          <tbody>
            <tr>
              <td><?php echo $entry['id']; ?></td>
              <td><?php echo $entry['name']; ?></td>
              <td><?php echo $entry['email']; ?></td>
              <td><?php echo $entry['gender']; ?></td>
              <td><?php echo $entry['position']; ?></td>
              <td><?php echo $entry['work']; ?></td>
              <td><?php echo htmlspecialchars($entry['question'], ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
          </tbody>
          <?php endforeach; ?>
        </table>

        <nav aria-label="Pagination" class="my-5">
          <ul class="pagination pagination-lg justify-content-center">
              <?php if($page > 1) : ?>
              <!-- クエリパラメータを変更して表示件数や昇順降順を変えても遷移時、遷移後に正常に動作させる -->
              <li class="page-item"><a class="page-link" href="?limit=<?php echo $perPage; ?>&page=<?php echo $page - 1; ?>&sort-column=<?php echo $sortColumn; ?>&direction=<?php echo $direction; ?>">前へ</a></li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
              <li class="page-item"><a class="page-link" href="?limit=<?php echo $perPage; ?>&page=<?php echo $i; ?>&sort-column=<?php echo $sortColumn; ?>&direction=<?php echo $direction; ?>"><?php echo $i; ?></a></li> 
              <?php endfor; ?>
              <?php if($page < $totalPages) : ?>
              <li class="page-item"><a class="page-link" href="?limit=<?php echo $perPage; ?>&page=<?php echo $page + 1; ?>&sort-column=<?php echo $sortColumn; ?>&direction=<?php echo $direction; ?>">次へ</a></li>
              <?php endif; ?>
          </ul>
        </nav>

        <form action="" method="GET" id="昇順降順の切り替え">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-lg-2 mb-2">
                <label for="表示件数" class="form-label">表示件数:</label>
                <select class="form-select" name="limit" id="表示件数">
                  <!-- selectedを使って指定した件数を固定 -->
                  <option value="5" <?php if ($perPage === 5): ?>selected<?php endif; ?>>5件</option>
                  <option value="10" <?php if ($perPage === 10): ?>selected<?php endif; ?>>10件</option>
                  <option value="15" <?php if ($perPage === 15): ?>selected<?php endif; ?>>15件</option>
                  <option value="20" <?php if ($perPage === 20): ?>selected<?php endif; ?>>20件</option>
                </select>
                <input type="hidden" name="page" value="<?php if(isset($page)) {echo $page;} ?>"> 
              </div>
            </div>

              <!-- 昇順降順のソート(並び替え)に関する情報をインプットする -->
            <div hidden class="row justify-content-center">  <!-- hiddenでdiv内の要素を隠す -->
              <div class="col-lg-2 mb-1">
                <label for="昇順降順" class="form-label"></label>
                <select class="form-select" name="sort-column" id="昇順降順">
                  <option value="id" <?php if ($sortColumn === 'id'): ?>selected<?php endif; ?>>id</option>
                  <option value="name" <?php if ($sortColumn === 'name'): ?>selected<?php endif; ?>>名前</option>
                  <option value="email" <?php if ($sortColumn === 'email'): ?>selected<?php endif; ?>>メールアドレス</option>
                  <option value="gender" <?php if ($sortColumn === 'gender'): ?>selected<?php endif; ?>>性別</option>
                  <option value="position" <?php if ($sortColumn === 'position'): ?>selected<?php endif; ?>>希望のポジション</option>
                  <option value="work" <?php if ($sortColumn === 'work'): ?>selected<?php endif; ?>>前職</option>
                  <option value="question" <?php if ($sortColumn === 'question'): ?>selected<?php endif; ?>>質問</option>
                </select>
                <input type="radio" class="form-check-input" name="direction" value = "asc" <?php if(isset($direction) && $direction === 'asc') {echo 'checked';}?>>
                <input type="radio" class="form-check-input" name="direction" value= "desc" <?php if(isset($direction) && $direction === 'desc') {echo 'checked';}?>>
              </div>
            </div>
              
            <div class="row justify-content-center">
              <div class="col-lg-2 text-center">
                <input class="form-control" type="submit""> 
              </div>
            </div>

          </div>
        </form>
    </header>           
  </body>
</html>
