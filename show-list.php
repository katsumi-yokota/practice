<?php
// 変数fileをつくる
$file = './entries.json';
if(!file_exists("$file"))
{
  echo 'ファイルがありません！';
  return;
}
// 変数fileの内容を文字列に取り込む
$json = file_get_contents($file);

// 変数jsonが空なら「JSONファイルがありません」を出力する
if(empty($json))
{
  echo 'JSONファイルがありません';
  return;
}

// 変数jsonが空なら'entries.json'を作成する
if(!empty($json))
{
  touch('entries.json');
}

// 変数jsonをjson_decodeでデコード（復号）
$entries = json_decode($json, true);

// 変数の情報を開示する
// var_dump($documents);

if(empty($entries)) 
{
  echo'空です！';
  return;
}
?>

<!DOCTYPE html>
<html>
  <head>
  <title>form.phpに入力された情報をテーブル化する</title> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  </head>

  <body>
  <table class="table container">
  <!-- HTMLのコメントアウト、PHPのコメントアウトの違いに注意 -->
  <!-- テーブルのヘッダー -->
  <thead>
    <tr>
      <!-- scopeは書かなくてもよい -->
      <!-- colはcolumn（カラム）のこと -->
      <th scope="row">#</th>
      <th scope="col">名前</th>
      <th scope="col">メールアドレス</th>
      <th scope="col">性別</th>
      <th scope="col">希望ポジション</th>
      <th scope="col">前職</th>
      <th scope="col">質問</th>
    </tr>
  </thead>
  <!-- テーブルのボディ -->
<tbody>
  <!-- 変数entriesから、1個ずつ値（name, email, gender, position, work, question）を取り出す -->
  <?php foreach ($entries as $i => $entry): ?>
    <tr>
      <!-- 可読性を高めるためにも、さっさと計算させる（算数の計算における「足すや掛ける」のように、順番がある）ためにも、こういう場合には（）を使う -->
      <!-- 変数iはValue。デフォルトで0から始まるので１を足してあげればよい -->
      <th><?php echo ($i+1); ?></th>
      <td><?php echo $entry['name']; ?></td>
      <td><?php echo $entry['email']; ?></td>
      <td><?php echo $entry['gender']; ?></td>
      <td><?php echo $entry['position']; ?></td>
      <td><?php echo $entry['work']; ?></td>
      <!-- htmlcharactersを用いてHTMLで使われる文字コードを無効化して、単なる文字列として出力する -->
      <!-- .や,を用いると複数の値をechoできるが、文字コードを無効化できなかったので以下のコードに決定 -->
      <td><?php echo nl2br(htmlspecialchars($entry['question'], ENT_QUOTES, 'UTF-8')); ?></td>
    </tr>
  <!-- HTMLの中にPHPを書く時に{}があるとどのブロックを閉じるのかがわかりにくいため、endforceが好んで用いられる -->
  <?php endforeach; ?>
  </tbody>
  </table>
  </body>
</html>
