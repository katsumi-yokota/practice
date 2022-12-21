<?php
// 変数fileをつくる
// 変数fileには、data.jsonという文字列が入っている（ファイルの中身が入っているわけではない！）
$file = './data.json';

if(!file_exists($file))
{
  echo 'ファイルがありません！';
  return;
}

// 変数fileの内容を文字列に読む込む
$json = file_get_contents($file);
if(empty($json)) 
{
  echo 'ファイルが空です！';
  return;
}
// 変数jsonをjson_decodeでデコード（復号）
$documents = json_decode($json, true);

// var_dumpで変数について調べる
//var_dump($documents); 

// 条件分岐
if (empty($documents)) {
  echo '空です！';
  return;
}

//foreachを使って、変数documentsから1個ずつ値(name, department, text)を取り出して変数documentに入れる
foreach ($documents as $document) 
{
  // EOT＝エンドオブテキスト
  print<<<EOT
  <div style="m10; p5; border; solid 1px black;">
  <p>名前:{$document['name']}</p>
  <p>所属:{$document['department']}</p>
  <p>
  EOT;
  // 以下をforeachを使って書き換える
  foreach($document['text'] as $line)
  {
    print<<<EOT
    {$line}<br>
  EOT;
  }
    print<<<EOT
  </p>
  </div>

  EOT;
}


?>
