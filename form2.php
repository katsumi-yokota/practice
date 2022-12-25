<!-- //$は変数名の指定に使う -->
<?php
//メッセージ、エラーメッセージを表示する
$message = '';
$error = '';
//ドルアンダーバーポスト
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
          //もしentries2.jsonが存在していないならば（！を用いているので「反対」の意味になる）
          if(!file_exists('entries2.json'))
          {
               //touch関数を用いてentries2.jsonをつくる
               touch('entries2.json');
          }

          $current_data = file_get_contents('entries2.json'); //ファイルの全内容を文字列に読み込む
          $array_data = json_decode($current_data, true); //json_decodeでJSON文字列をデコード(復号)する
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
          if(file_put_contents('entries2.json', $final_data)) 
          {
               $message = '<p class="text-center text-success display-4">エントリー完了!お疲れ様でした。担当者がご連絡します。</p>';
          }
     }
}
?>
<!DOCTYPE html>
<html>
<head>
         <title>[DB用]Shimoningにエントリーするしない？</title>   
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
     <div class="container">
          <h3 class="text-center">[DB用]Shimoningにエントリーするしない？</h3>
          <h5 class="text-center"><span class='text-danger'>※</span> は必須</h5>
          <!-- action属性は、form要素の中の属性の1つで、フォームに入力された情報の送信先を指定する。今回はform-db.phpを指定 -->
          <form method="post" action="form-db.php"> 
               <?php
               if(!empty($error)) //isset関数とは、引数に指定した変数に値が設定されている、かつ、NULLではない場合にはtrue(正)の値を戻り値とします。 それ以外は、戻り値にfalse(偽)の値を返します。
               {
                    echo $error;
               }
               ?>
               <label class="py-4">お名前<span class="text-danger"> ※</span></label>
               <input type="text" name="name"  placeholder="田中太郎" class="form-control" />
               <label class="py-4">メールアドレス<span class="text-danger"> ※</span></label>
               <!-- <input type="email">を使うことで、バリデーション（エラーチェック）が効く -->
               <input type="email" name="email" placeholder="shimoning@gmail.com" class="form-control" />
               <label class="py-4">性別</label>
               <!-- 性別をプルダウンにする -->
               <select name="gender" class="container">
                    <!-- <option selected>で一番初めの文章（この場合は「選択してください」）が表示される -->
                    <option selected>選択してください</option>
                    <option value="男性">男性</option>
                    <option value="女性">女性</option>
                    <option value="その他">その他</option>
                    <option value="未回答">未回答</option>
               </select>
               <label class="py-4">ご希望のポジション<span class='text-danger'> ※</span></label>
               <input type="text" name="position"  placeholder="SE" class="form-control" />
               <label class="py-4">前職</label>
               <input type="text" name="work" placeholder="保険の営業" class="form-control" />
               <label class="py-4">質問（事前にきいておきたいこと）</label>
               <textarea class="container" name="question" cols="50" rows="5" placeholder="Shimoningの名前の由来は？"></textarea>
               <input type="submit" name="submit" value="エントリーする" class="btn btn-success px-5" />
               <?php
               if(!empty($message))
               {
                    echo $message;
               }
               ?>
          </form>
     </div>
</body>
<footer class='text-center'>(c) shimoning.com</footer>
</html>
