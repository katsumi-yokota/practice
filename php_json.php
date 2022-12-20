<?php
// 変数fileをつくる
$file = './data.json';
// 変数fileの内容を文字列に読む込む
$json = file_get_contents($file);
if(empty($json)) {
  echo 'JSONファイルがありません！';
  return;
}
// 変数jsonをjson_decodeでデコード（復号）
$data = json_decode($json, true);

// var_dumpで変数について調べる
//var_dump($data); 

// 条件分岐
if (empty($data)) {
  echo '空です！' . PHP_EOL;
  return;
}

//foreach&連想配列
$dataa = [
        '名前' => '$data['name']',
        '所属' => '$data['department'],
        'メッセージ' => '$data['text']
];

foreach ($dataa as $key => $value) {
  echo $value. '<br/>';
}

// forを使って繰り返し変数dataを数える…と説明されているが、Countable（数えられる）または配列以外の型の値を指定した際に発生するエラーが発生する
for($i = 0; $i < count($data); $i++){
  // EOT＝エンドオブテキスト？
    print<<<EOT
    <div style="m10; p5; border; solid 1px black;">
        <p>名前:{$data[$i]['name']}</p>
        <p>所属:{$data[$i]['department']}</p>
        <p>

EOT;
// forのなかのfor。すでに変数iはあるので、変数kを用いる
        for($k = 0; $k < count($data[$i]['text']); $k++){
            print<<<EOT
            {$data[$i]['text'][$k]}<br>
EOT;
        }
        print<<<EOT
        </p>
    </div>

EOT;
}

// }

?>
