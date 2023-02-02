<?php
//メッセージ
$noName = '';
$noEmail = '';
$noPosition = '';

// 必須項目
$dangerName = filter_input(INPUT_POST, 'name');
$dangerEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$dangerPosition = filter_input(INPUT_POST, 'position');

// デバック用
echo $dangerName . '名前' . '<br>';
echo $dangerEmail . 'メールアドレス' . '<br>';
echo $dangerPosition . 'ポジション';

if(empty($dangerName))
{
     $noName = 'お名前を入力してください';
}
if(empty($dangerEmail))
{
     $noEmail = '正しいメールアドレスを入力してください';
}
if(empty($dangerPosition))
{
     $noPosition = 'ご希望のポジションを入力してください';
}
?>

<!DOCTYPE html>
<html>
<head>
     <title>Shimoningにエントリーする</title>   
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
     <div class="container">
          <h1 class="text-center">Shimoningにエントリーする</h3>
          <p class="text-center"><span class='text-danger'>※</span> は必須</p>

          <!-- エラーメッセージ -->
          <?php if (empty($dangerName)): ?> 
          <p class="alert alert-warning"><?php echo $noName; ?></p>
          <?php endif; ?>
          <?php if (empty($dangerEmail)): ?>
          <p class="alert alert-warning"><?php echo $noEmail; ?></P>
          <?php endif; ?>
          <?php if (empty($dangerPosition)): ?>
          <p class="alert alert-warning"><?php echo $noPosition; ?></p>
          <?php endif; ?>

          <form method="post" action="<?php if (!empty($dangerName) && !empty($dangerEmail) && !empty($dangerPosition)) {echo 'form-complete.php';} ?>">
               <label class="py-4" for="name">お名前<span class="text-danger"> ※</span></label>
               <input type="text" name="name" id="name" placeholder="田中太郎" class="form-control" value="<?php echo $dangerName; ?>">
               <label class="py-4" for="email">メールアドレス<span class="text-danger"> ※</span></label>
               <input type="text" name="email" id="email" placeholder="shimoning@gmail.com" class="form-control" value="<?php echo filter_input(INPUT_POST, 'email'); ?>">
               <label class="py-4" for="gender">性別</label>
               <select name="gender" id="gender" class="container">
                    <option selected>選択してください</option>
                    <option value="男性">男性</option>
                    <option value="女性">女性</option>
                    <option value="その他">その他</option>
                    <option value="未回答">未回答</option>
               </select>
               <label class="py-4" for="position">ご希望のポジション<span class='text-danger'> ※</span></label>
               <input type="text" name="position" id="position" placeholder="SE" class="form-control" value="<?php echo $dangerPosition; ?>">
               <label class="py-4" for="work">前職</label>
               <input type="text" name="work" id="work" placeholder="保険の営業" class="form-control">
               <label class="py-4" for="question">質問（事前にきいておきたいこと）</label>
               <textarea class="container" name="question" id="question" cols="50" rows="5" placeholder="Shimoningの名前の由来は？"></textarea>
               <label class="py-4" for="annual_income">希望年収（単位:万円）</label>
               <input type="text" name="annual_income" id="annual_income" placeholder="100" class="form-control">
               <input type="submit" name="submit" value="エントリーする" class="btn btn-success px-5 my-4">
          </form>
     </div>
     <footer class='text-center'>&copy; shimoning.com</footer>
</body>
</html>
