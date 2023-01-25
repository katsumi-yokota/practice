<?php
$pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t');

$perPage = 5; // デフォルトで１ページ５件を表示
$currentPage = 1; // デフォルトで1ページ目を表示

if(isset($_GET['limit']))
{
  $perPage = (int)$_GET['limit'];
}

// 0以下の場合は5件表示
if ($perPage <= 0)
{
  $perPage = 5;
}

// 独自関数sanitizeColumnをつくってサニタイズ
$id = '';
$name = '';
$email = '';
$gender = '';
$position = '';
$work = '';
$question = '';
$anuualIncome = '';
function sanitizeColumn($value)
{
  if (false !== strpos($value, '%'))
  {
    $value = str_replace('%', '\\%', $value); // %をサニタイズ
  }
  if (false !== strpos($value, '_'))
  {
    $value = str_replace('_', '\\_', $value); // _をサニタイズ
  }
  return $value;
}

$columns = ['id', 'name', 'email', 'gender', 'position', 'work', 'question', 'annual_income']; 
// カラム名を、エントリー画面と同じにする
$labels = [
  'id' => 'id',
  'name' => 'お名前',
  'email' => 'メールアドレス',
  'gender' => '性別',
  'position' => '希望ポジション',
  'work' => '前職',
  'question' => '質問',
  'annual_income' => '希望年収',
];
$values = [];
foreach ($columns as $column)
{
  if (isset($_GET[$column]))
  {
    $values[$column] = sanitizeColumn($_GET[$column]);
  }
}

$conditions = [];
foreach ($values as $key => $value)
{
  if (!empty($value))
  {
    $conditions[] = "{$key} LIKE :{$key}";
  }
}

$where = '';
if (!empty($conditions))
{
  $where = ' WHERE ' . implode(' AND ', $conditions);
}

// 検索結果次第でページ数を変更
$sql = 'SELECT COUNT(*) AS entry_count FROM entries' . $where;

$stmt = $pdo->prepare($sql);
foreach ($values as $key => $value)
{
  if (!empty($value))
  {
    $stmt->bindValue(":{$key}", "%{$value}%", PDO::PARAM_STR);
  }
}
$stmt->execute();
$total = $stmt->fetch(PDO::FETCH_ASSOC);
$totalPages = ceil($total['entry_count'] / $perPage); // $totalPagesに、$total['entry_count']で全エントリー数を取得して、１ページあたりの表示件数で割って、ceilで切り上げた数字を格納

// 存在するページが入力された場合はそのページに遷移
if(isset($_GET['page']) && preg_match('/^[1-9][0-9]*$/', $_GET['page']) && $_GET['page'] <= $totalPages) 
{
  $currentPage = (int)$_GET['page'];
}

$direction = 'asc'; 
if(isset($_GET['direction']) && $_GET['direction'] === 'desc')
{
  $direction = 'desc';
}

// カラム名の配列をつくり、in_arrayで配列にカラム名があるかチェックして、ある場合はそのカラム名を$sortColumnに格納
$sortColumn = 'id'; 
if(isset($_GET['sort-column']) && in_array($_GET['sort-column'], $columns, true)) // 第三引数はtrueにして厳密に比較
{
  $sortColumn = $_GET['sort-column'];
}

$offset = $perPage * ($currentPage - 1);

$sql = 'SELECT * FROM entries'.$where.' ORDER BY '.$sortColumn.' '.$direction.' LIMIT :offset, :limit';

$stmt = $pdo->prepare($sql);
foreach ($values as $key => $value)
{
  if (!empty($value))
  {
    $stmt->bindValue(":{$key}", "%{$value}%", PDO::PARAM_STR);
  }
}

// 共通部分
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ソート及びページネーション機能の共通化用
$parametersForSort = ['limit' => $perPage, 'page' => $currentPage]; // ソート用
$parametersForPagination = ['limit' => $perPage, 'direction' => $direction, 'sort-column' => $sortColumn]; // ページネーション用

foreach ($values as $column => $value)
{
  if (empty($value))
  {
    continue; // emptyなら処理を終わらせて次に進む
  }
  // emptyでなければ、次の処理をする
  $parametersForSort[$column] = $value;
  $parametersForPagination[$column] = $value;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="test-paging-style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>エントリー一覧</title>
</head>

  <body>
    <header>
      <h1 class="text-center">エントリー一覧</h1>
    </header>

    <table class="table table-bordered table-striped table-hover">
      <thead>
          <tr>
          <?php foreach ($columns as $column): ?>
            <th>
              <!-- ソート機能の共通化 -->
              <?php if ($column !== $sortColumn): ?> <!-- ワンクリック目は昇順にソート -->
              <a href="entries.php?<?php echo http_build_query($parametersForSort); ?>&sort-column=<?php echo $column; ?>&direction=asc">
              <?php echo $labels[$column]; ?>
              </a>
              <?php elseif ($direction === 'asc'): ?> <!-- 昇順の時は降順にソート-->
              <a href="entries.php?<?php echo http_build_query($parametersForSort); ?>&sort-column=<?php echo $column; ?>&direction=desc">
              <?php echo $labels[$column]; ?>↑
              </a>
              <?php else: ?> <!-- 降順の時（クリックされたカラムであり、かつ昇順でない時）は降順にソート -->
              <a href="entries.php?<?php echo http_build_query($parametersForSort); ?>&sort-column=<?php echo $column; ?>&direction=asc">
              <?php echo $labels[$column]; ?>↓
              </a>
              <?php endif; ?>
            </th>
          <?php endforeach; ?>
          </tr>
      </thead>

      <tbody>
      <?php foreach ($entries as $entry): ?>
          <tr>
            <td><?php echo $entry['id']; ?></td>
            <td><?php echo $entry['name']; ?></td>
            <td><?php echo $entry['email']; ?></td>
            <td><?php echo $entry['gender']; ?></td>
            <td><?php echo $entry['position']; ?></td>
            <td><?php echo $entry['work']; ?></td>
            <td><?php echo htmlspecialchars($entry['question'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo $entry['annual_income'] . '万円'; ?></td>
          </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
      <!-- ページネーション -->
      <nav aria-label="Page navigation" class="my-5">
        <ul class="pagination pagination-lg justify-content-center">
          <?php if($currentPage > 1): ?>
          <li class="page-item">
            <a class="page-link" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $currentPage - 1; ?>">前へ
            </a>
          </li>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?php if ($currentPage === $i) {echo 'disabled';} ?>"> <!-- 開いているページのみclass="disabled"にしてクリック不能化 -->
            <a class="page-link <?php if ($currentPage === $i) {echo 'active';} ?>" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $i; ?>">
            <?php echo $i; ?>
            </a>
          </li>
          <?php endfor; ?>
          <?php if($currentPage < $totalPages): ?>
          <li class="page-item">
            <a class="page-link" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $currentPage + 1; ?>">次へ
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </nav>

      <form action="" method="GET">
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
              <input type="hidden" name="page" value="<?php if(isset($currentPage)) {echo $currentPage;} ?>"> 
            </div>
          </div>

          <!-- 昇順降順のソート -->
          <div hidden class="row justify-content-center">  <!-- hiddenでdiv内の要素を隠す -->
            <div class="col-lg-2 mb-2">
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
            
          <!-- 検索フォーム -->
          <div class="row justify-content-center">
            <div class="col-lg-2 text-center">
              <input type="number" class="my-2 form-control" name="id" placeholder="id" value="<?php if (!empty($values)) {echo $values['id'];}?>">
              <input type="text" class="my-2 form-control" name="name" placeholder="名前" value="<?php if (!empty($values)) {echo $values['name'];}?>">
              <input type="text" class="my-2 form-control" name="email" placeholder="メールアドレス" value="<?php if (!empty($values)) {echo $values['email'];}?>">
              <input type="text" class="my-2 form-control" name="gender" placeholder="性別" value="<?php if (!empty($values)) {echo $values['gender'];}?>"> 
              <input type="text" class="my-2 form-control" name="position" placeholder="希望ポジション" value="<?php if (!empty($values)) {echo $values['position'];}?>">
              <input type="text" class="my-2 form-control" name="work" placeholder="前職" value="<?php if (!empty($values)) {echo $values['work'];}?>"> 
              <input type="text" class="my-2 form-control" name="question" placeholder="質問" value="<?php if (!empty($values)) {echo $values['question'];}?>">
              <input type="number" class="my-2 form-control" name="annual_income" placeholder="希望年収（万円）" value="<?php if (!empty($values)) {echo $values['annual_income'];}?>">
              <input class="my-2 form-control" type="submit">
            </div>
          </div>

        </div>
      </form>
  </body>
</html>
