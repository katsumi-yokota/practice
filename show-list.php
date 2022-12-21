<?php
// 変数fileをつくる
$file = './entries1.json';
if(!file_exists('$file'))
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

// 変数entriesから、1個ずつ値（name, email, gender, position, work, question）を取り出す
foreach ($entries as $entry) 
{
  // ヒアドキュメント。終端記号は必ず文の最初に書く
  print<<<EOT
  <p>名前:{$entry['name']}</p>
  <p>メールアドレス:{$entry['email']}</p>
  <p>性別:{$entry['gender']}</p>
  <p>希望ポジション:{$entry['position']}</p>
  <p>前職:{$entry['work']}</p>
  <p>質問:{$entry['question']}</p>
  EOT;
}
?>
