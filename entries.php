<?php
session_start();

// 自動ログアウト
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800))
{
  $_SESSION = array();
  http_response_code(200); // 要検討
  header("Location: timeout.php");
  exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // 最終アクティビティを記録

if (!isset($_SESSION['username']))
{
  return;
}
else
{
  $welcomeMessage = 'ようこそ ';

  $pdo = new PDO('mysql:dbname=form-db;host=localhost;charset=utf8', 'yamadasan', '1q2w3e4r5t');

  $perPage = 5; // デフォルトで１ページ５件を表示
  $perPage = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 5; // NULLなら5件
  $currentPage = 1; // デフォルトで1ページ目を表示

  // 0以下の場合は5件表示
  if ($perPage <= 0)
  {
    $perPage = 5;
  }

  // 独自関数sanitizeColumnをつくってサニタイズ
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
  $labels = 
  [
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
    $values[$column] = sanitizeColumn(filter_input(INPUT_GET, $column));
  }

  // 範囲検索用
  $messageForIncomeMin = '';
  $messageForIncomeMax = ''; 
  $messageForIncomeCompare = '';
  $annualIncomeMin = '';
  $annualIncomeMax = '';

  // 値を取得し格納。値が存在しない場合は空の文字列を格納
  // $annualIncomeMin、Maxに格納する前に、クッションとして別の変数に格納
  $dirtyAnnualIncomMin = filter_input(INPUT_GET, 'annual_income_min', FILTER_VALIDATE_INT) ?? '';
  $dirtyAnnualIncomMax = filter_input(INPUT_GET, 'annual_income_max', FILTER_VALIDATE_INT) ?? '';

  if (!empty($dirtyAnnualIncomMin) && !preg_match('/^[0-9]*$/', $dirtyAnnualIncomMin)) 
  {
    $messageForIncomeMin = '下限希望年収には、0以上の正の整数を入力してください。<例> 300';
  } 
  elseif (!empty($dirtyAnnualIncomMax) && !preg_match('/^[1-9][0-9]*$/', $dirtyAnnualIncomMax))
  {
    $messageForIncomeMax = '上限希望年収には、1以上の正の整数を入力してください。<例> 700';
  } 
  elseif ($dirtyAnnualIncomMin && $dirtyAnnualIncomMax && $dirtyAnnualIncomMin > $dirtyAnnualIncomMax) 
  {
    $messageForIncomeCompare = '上限希望年収には下限希望年収より大きい数字を入力してください。';
  } 
  else
    if (!empty($dirtyAnnualIncomMin))
    {
      $annualIncomeMin = $dirtyAnnualIncomMin;
    }
    if (!empty($dirtyAnnualIncomMax))
    {
      $annualIncomeMax = $dirtyAnnualIncomMax;
    }

  $conditions = [];
  foreach ($values as $key => $value)
  {
    if (!empty($value))
    {
      $conditions[] = "{$key} LIKE :{$key}";
    }
  }

  if (preg_match('/^[0-9]+$/', $annualIncomeMin))
  {
    $conditions[] = 'annual_income >= :annual_income_min';
  }
  if (preg_match('/^[1-9][0-9]+$/', $annualIncomeMax))
  {
    $conditions[] = 'annual_income <= :annual_income_max';
  }

  $where = '';
  if (!empty($conditions))
  {
    $where = ' WHERE ' . implode(' AND ', $conditions);
  }

  // 検索結果に応じてページ数を変更
  $sql = 'SELECT COUNT(*) AS entry_count FROM entries' . $where;
  $stmt = $pdo->prepare($sql);

  foreach ($values as $key => $value)
  {
    if (!empty($value))
    {
      $stmt->bindValue(":{$key}", "%{$value}%", PDO::PARAM_STR);
    }
  }

  if (!empty($annualIncomeMin))
  {
    $stmt->bindValue(':annual_income_min', $annualIncomeMin, PDO::PARAM_INT);
  }
  if (!empty($annualIncomeMax))
  {
    $stmt->bindValue(':annual_income_max', $annualIncomeMax, PDO::PARAM_INT);
  }

  $stmt->execute();
  $total = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalPages = ceil($total['entry_count'] / $perPage); // 全ページ数を、$total['entry_count']で全エントリー数を取得して、１ページあたりの表示件数で割って、ceilで切り上げた数字を$totalPages格納

  // 存在するページが入力された場合はそのページに遷移
  $dangerCurrentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
  if(preg_match('/^[1-9][0-9]*$/', $dangerCurrentPage) && $dangerCurrentPage <= $totalPages)
  {
    $currentPage = $dangerCurrentPage;
  }

  $direction = 'asc';
  $dangerDirection = filter_input(INPUT_GET, 'direction');
  if($dangerDirection && $dangerDirection === 'desc')
  {
    $direction = $dangerDirection;
  }

  // カラム名の配列をつくり、in_arrayで配列にカラム名があるかチェックして、ある場合はそのカラム名を$sortColumnに格納
  $sortColumn = 'id';
  $dangerSortColumn = filter_input(INPUT_GET, 'sort-column');
  if($dangerSortColumn && in_array($dangerSortColumn, $columns, true)) // 第三引数はtrueで厳密に比較
  {
    $sortColumn = $dangerSortColumn;
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

  if (!empty($annualIncomeMin))
  {
    $stmt->bindValue(':annual_income_min', $annualIncomeMin, PDO::PARAM_INT);
  }
  if (!empty($annualIncomeMax))
  {
    $stmt->bindValue(':annual_income_max', $annualIncomeMax, PDO::PARAM_INT);
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
    // emptyでなければ、変数に$valueを格納
    $parametersForSort[$column] = $value;
    $parametersForPagination[$column] = $value;
  }

  // URLパラメータ削除用
  $baseUrl = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
  ?>
  <!DOCTYPE html>
  <html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- <meta http-equiv="refresh" content="1800;URL=login.php"> --> <!-- メタリフレッシュ -->
    <link rel="stylesheet" href="test-paging-style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>エントリー一覧</title>
  </head>

    <body>
      <header>
        <!-- ログイン、ログアウト -->
        <?php if (!empty($welcomeMessage)): ?> 
        <p><?php echo $welcomeMessage . $_SESSION['username'] . ' 様'; ?></p>
        <a href="logout.php" class="btn btn-success">ログアウト</a>
        <?php endif; ?>

        <h1 class="text-center">エントリー一覧</h1>

        <!-- URLパラメータ削除ボタン -->
        <a class="btn btn-info mb-2" href="<?php echo $baseUrl; ?>">URLパラメータを削除</a>

        <!-- エラーメッセージ -->
        <?php if (!empty($messageForIncomeMin)): ?> 
        <p class="alert alert-warning"><?php echo $messageForIncomeMin; ?></p>
        <?php endif; ?>
        <?php if (!empty($messageForIncomeMax)): ?>
        <p class="alert alert-warning"><?php echo $messageForIncomeMax; ?></P>
        <?php endif; ?>
        <?php if (!empty($messageForIncomeCompare)): ?> <!-- 上限、下限の比較 -->
        <p class="alert alert-danger"><?php echo $messageForIncomeCompare; ?></p>
        <?php endif; ?>

      </header>

      <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
            <?php foreach ($columns as $column): ?>
              <th>
                <!-- ソート -->
                <?php if ($column !== $sortColumn): ?> <!-- ワンクリック目は昇順 -->
                <a href="entries.php?<?php echo http_build_query($parametersForSort); ?>&sort-column=<?php echo $column; ?>&direction=asc">
                <?php echo $labels[$column]; ?>
                </a>
                <?php elseif ($direction === 'asc'): ?> <!-- 昇順の時は降順-->
                <a href="entries.php?<?php echo http_build_query($parametersForSort); ?>&sort-column=<?php echo $column; ?>&direction=desc">
                <?php echo $labels[$column]; ?>↑
                </a>
                <?php else: ?> <!-- 降順の時（クリックされたカラムであり、かつ昇順でない時）は降順 -->
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
              <td><?php echo htmlspecialchars($entry['id']); ?></td>
              <td><?php echo htmlspecialchars($entry['name']); ?></td>
              <td><?php echo htmlspecialchars($entry['email']); ?></td>
              <td><?php echo htmlspecialchars($entry['gender']); ?></td>
              <td><?php echo htmlspecialchars($entry['position']); ?></td>
              <td><?php echo htmlspecialchars($entry['work']); ?></td>
              <td><?php echo htmlspecialchars($entry['question'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($entry['annual_income']) . '万円'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <!-- ページネーション -->
      <nav aria-label="Page navigation" class="my-5">
        <ul class="pagination pagination-lg justify-content-center">
          <li class="page-item <?php if($currentPage === 1){echo 'disabled';} ?>">
            <a class="page-link <?php if($currentPage === 1){echo 'text-secondary active';} ?>" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $currentPage - 1; ?>">
              ← Previous
            </a>
          </li>
          <?php if($currentPage !== 1 && $currentPage !== 2): ?>
          <li class="page-item">
            <a class="page-link" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=1">
              1
            </a>
          </li>
          <?php endif; ?>
          <?php if ($currentPage > 3): ?>
          <li class="page-item disabled">
            <a class="page-link" href="">...</a>
          </li>
          <?php endif; ?>
          <?php for ($i = max($currentPage - 1, 1); $i <= min($currentPage + 1, $totalPages); $i++): ?>
          <li class="page-item <?php if ($currentPage === $i) {echo 'disabled  border border-dark';} ?>">
            <a class="page-link <?php if ($currentPage === $i) {echo 'active border border-dark text-dark fw-bold';} ?>" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $i; ?>">
            <?php echo $i; ?>
            </a>
          </li>
          <?php endfor; ?>
          <?php if ($currentPage < $totalPages - 2): ?>
          <li class="page-item disabled">
            <a class="page-link" href="">...</a>
          </li>
          <li class="page-item">
            <a class="page-link" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $totalPages; ?>">
            <?php echo $totalPages; ?>
            </a>
          </li>
          <?php endif; ?>
          <li class="page-item <?php if($currentPage === (int)$totalPages){echo 'disabled';} ?>">
            <a class="page-link <?php if($currentPage === (int)$totalPages){echo 'text-secondary active';} ?>" href="entries.php?<?php echo http_build_query($parametersForPagination); ?>&page=<?php echo $currentPage + 1; ?>">
              Next →
            </a>
          </li>
        </ul>
      </nav>

      <form action="" method="get">
        <div class="container">
          <!-- 表示件数 -->
          <div class="row justify-content-center">
            <div class="col-lg-2 mb-2">
              <label for="表示件数" class="form-label">表示件数:</label>
              <select class="form-select" name="limit" id="表示件数">
                <option value="5" <?php if ($perPage === 5): ?>selected<?php endif; ?>>5件</option>
                <option value="10" <?php if ($perPage === 10): ?>selected<?php endif; ?>>10件</option>
                <option value="15" <?php if ($perPage === 15): ?>selected<?php endif; ?>>15件</option>
                <option value="20" <?php if ($perPage === 20): ?>selected<?php endif; ?>>20件</option>
              </select>
              <input type="hidden" name="page" value="<?php if(isset($currentPage)) {echo $currentPage;} ?>"> 
            </div>
          </div>

          <!-- ソート -->
          <div hidden class="row justify-content-center">
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
              <input type="text" class="my-2 form-control" name="id" placeholder="id" value="<?php if (!empty($values['id'])) {echo htmlspecialchars($values['id']);}?>">
              <input type="text" class="my-2 form-control" name="name" placeholder="名前" value="<?php if (!empty($values['name'])) {echo htmlspecialchars($values['name']);}?>">
              <input type="text" class="my-2 form-control" name="email" placeholder="メールアドレス" value="<?php if (!empty($values['email'])) {echo htmlspecialchars($values['email']);}?>">
              <input type="text" class="my-2 form-control" name="gender" placeholder="性別" value="<?php if (!empty($values['gender'])) {echo htmlspecialchars($values['gender']);}?>"> 
              <input type="text" class="my-2 form-control" name="position" placeholder="希望ポジション" value="<?php if (!empty($values['position'])) {echo htmlspecialchars($values['position']);}?>">
              <input type="text" class="my-2 form-control" name="work" placeholder="前職" value="<?php if (!empty($values['work'])) {echo htmlspecialchars($values['work']);}?>"> 
              <input type="text" class="my-2 form-control" name="question" placeholder="質問" value="<?php if (!empty($values['question'])) {echo htmlspecialchars(nl2br($values['question']));}?>">
              <input type="text" class="my-2 form-control" name="annual_income_min" placeholder="下限希望年収（万円）" value="<?php echo htmlspecialchars(filter_input(INPUT_GET, 'annual_income_min'));?>">
              <input type="text" class="my-2 form-control" name="annual_income_max" placeholder="上限希望年収（万円）" value="<?php echo htmlspecialchars(filter_input(INPUT_GET, 'annual_income_max'));?>">
              <input class="my-2 form-control" type="submit">
            </div>
          </div>

        </div>
      </form>
    </body>
  </html>
<?php
}
