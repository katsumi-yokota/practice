<!-- //$は変数名の指定に使う -->
<?php
//メッセージ、エラーメッセージを表示する
$message = '';
$error = '';
//読み方は「ドルアンダーバーポスト」
//スーパーグローバル変数はなるべく使わないのが望ましい
//変数がセットされているか、NULLではないかを確認するにはisset関数を用いる
if(isset($_POST['submit']))
{
     //もし名前欄が空欄なら
     if(empty($_POST['name']))
     {
          $error = "<p class='text-warning text-center display-5'>お名前が入力されていません</p>";
     }
     //もしメールアドレス欄が空欄なら
     else if(empty($_POST['email']))
     {
          $error = "<p class='text-warning text-center display-5'>メールアドレスが入力されていません</p>";
     }
     //もし希望するポジション欄が空欄なら
     else if(empty($_POST['position']))
     {
          $error = "<p class='text-warning text-center display-5'>ご希望のポジションが入力されていません</p>";
     }
     else
     {
          //もしJSONファイルが存在していないならば（<！>を用いているので反対の意味になる）
          if(!file_exists('entries.json'))
          {
               //touch関数を用いてJSONファイルをつくる
               touch('entries.json');
          }

          //ファイルの全内容を文字列に読み込む
          $current_data = file_get_contents('entries.json');
          //json_decodeでJSON文字列をデコード(復号)する
          $array_data = json_decode($current_data, true); 
          //連想配列
          //$_POSTはスーパーグローバル変数（定義済み関数）で、HTTP POSTメソッドで送信された値を取得する       
          $extra = array(
               'name' => $_POST['name'],
               'email' => $_POST['email'],
               'gender' => $_POST['gender'],
               'position' => $_POST['position'],
               'work' => $_POST['work'],
               'question' => $_POST['question'],
          );
          $array_data[] = $extra;
          $final_data[] = json_encode($array_data);
          //データをファイルに書き込む
          if(file_put_contents('entries.json', $final_data)) 
          {
               $message = '<p class="text-center text-success display-4">エントリー完了!お疲れ様でした。担当者がご連絡します。</p>';
          }
     }
}
?>
<!DOCTYPE html>
<html>
<head>
         <title>Shimoningにエントリーするしない？</title>  
         <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>  
         <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" /> 
         <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
         <!-- Bootstrap CSS -->
         <!-- Bootstrap CSS -->
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
     <div class="container">
          <h3 class="text-center">Shimoningにエントリーする</h3>
          <h5 class="text-center"><span class='text-danger'>※</span> は必須</h5>
          <form method="post">
               <?php
               if(!empty($error)) //isset関数とは、引数に指定した変数に値が設定されている、かつ、NULLではない場合にはtrue(正)の値を戻り値とします。 それ以外は、戻り値にfalse(偽)の値を返します。
               {
                    echo $error;
               }
               ?>
               <label class="p-3">お名前<span class="text-danger"> ※</span></label>
               <input type="text" name="name"  placeholder="田中太郎" class="form-control" />
               <label class="p-3">メールアドレス<span class="text-danger"> ※</span></label>
               <input type="text" name="email"  placeholder="shimoning@gmail.com" class="form-control" />
               <label class="p-3">性別</label>
               <input type="text" name="gender" placeholder="男性" class="form-control" />
               <label class="p-3">ご希望のポジション<span class='text-danger'> ※</span></label>
               <input type="text" name="position"  placeholder="SE" class="form-control" />
               <label class="p-3">前職</label>
               <input type="text" name="work" placeholder="保険の営業" class="form-control" />
               <label class="p-3">事前にきいておきたいこと</label>
               <textarea class="container" name="question" cols="50" rows="5" placeholder="Shimoningの名前の由来は？"></textarea>
               <input type="submit" name="submit" value="エントリーする" class="btn btn-success b-3" />
               <?php
               if(!empty($message))
               {
                    echo $message;
               }
               ?>
          </form>
     </div>

     //テーブルをつくって見やすく表示する
     <div class="container" style="width: 555px;">
     <div class="table-container">
     <?php
		if(isset($message))
		{
			echo $message;
		?>
     <table id="tbstyle">
          <tbody>
               <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>性別</th>
                    <th>希望ポジション</th>
                    <th>前職</th>
                    <th>事前質問</th>
               </tr>
               <?php foreach ($entries as $entry) { ?>
               <tr>
                    <td> <?= $entry->name; ?> </td>
                    <td> <?= $entry->email; ?> </td>
                    <td> <?= $entry->gender; ?> </td>
                    <td> <?= $entry->position; ?> </td>
                    <td> <?= $entry->work; ?> </td>
                    <td> <?= $entry->question; ?> </td>
               </tr>
               <?php }
          }
          else
               echo $message;
          ?>
          </tbody>
     </table> 
     </div>
     </div>
</body>
<footer class='text-center'>(c) shimoning.com</footer>
</html>
