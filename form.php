<?php
//セッション
session_start();

//バリデーション用
$dangerName = filter_input(INPUT_POST, 'name');
$dangerEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$dangerPositions = filter_input(INPUT_POST, 'positions', FILTER_DEFAULT, ['flags' => FILTER_REQUIRE_ARRAY]) ?? [];
$dangerGender = filter_input(INPUT_POST, 'gender');
$dangerWork = filter_input(INPUT_POST, 'work');
$dangerQuestion = filter_input(INPUT_POST, 'question');
$dangerAnnualIncome = filter_input(INPUT_POST, 'annual_income');
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
          <?php if (!empty($_SESSION['messageForName'])):?>
          <p class="alert alert-warning"><?php echo $_SESSION['messageForName']; ?></p>
          <?php endif; ?>
          <?php if (!empty($_SESSION['messageForEmail'])):?>
          <p class="alert alert-warning"><?php echo $_SESSION['messageForEmail']; ?></p>
          <?php endif; ?>
          <?php if (!empty($_SESSION['messageForPositions'])):?>
          <p class="alert alert-warning"><?php echo $_SESSION['messageForPositions']; ?></p>
          <?php endif; ?>

          <form method="post" action="form-complete.php">
               <label class="py-4" for="name">お名前<span class="text-danger"> ※</span></label>
               <input type="text" name="name" id="name" placeholder="田中太郎" class="form-control" value="<?php if (isset($_SESSION['name'])){ echo htmlspecialchars($_SESSION['name']);} ?>">
               <label class="py-4" for="email">メールアドレス<span class="text-danger"> ※</span></label>
               <input type="text" name="email" id="email" maxlength="254" placeholder="shimoning@gmail.com" class="form-control" value="<?php if (isset($_SESSION['email'])) {echo htmlspecialchars($_SESSION['email']);} ?>">
               <label class="py-4" for="gender">性別</label>
               <select name="gender" id="gender" class="form-control">
                    <option selected>選択してください</option>
                    <option value="男性" <?php if (isset($_SESSION['gender']) && $_SESSION['gender'] === '男性'){echo 'selected';}?>>男性</option>
                    <option value="女性" <?php if (isset($_SESSION['gender']) && $_SESSION['gender'] === '女性'){echo 'selected';}?>>女性</option>
                    <option value="その他" <?php if (isset($_SESSION['gender']) && $_SESSION['gender'] === 'その他'){echo 'selected';}?>>その他</option>
                    <option value="未回答" <?php if (isset($_SESSION['gender']) && $_SESSION['gender'] === '未回答'){echo 'selected';}?>>未回答</option>
               </select>
               <label class="py-4" name="positions[]" for="position" class="form-check is-invalid">ご希望のポジション（複数選択可）<span class='text-danger'> ※</span></label>
               <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="positions[]" value="SE" id="SE" <?php if (isset($_SESSION['positions']) && in_array('SE', $_SESSION['positions'])){echo 'checked';} ?>>
                    <label class="form-check-label" for="SE">
                         SE
                    </label>
               </div>

               <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="positions[]" value="プログラマー" id="programmer" <?php if (isset($_SESSION['positions']) && in_array('プログラマー', $_SESSION['positions'])){echo 'checked';} ?>>
                    <label class="form-check-label" for="programmer">
                         プログラマー
                    </label>
               </div>

               <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="positions[]" value="インフラエンジニア" id="inflaengineer" <?php if (isset($_SESSION['positions']) && in_array('インフラエンジニア', $_SESSION['positions'])){echo 'checked';} ?>>
                    <label class="form-check-label" for="inflaengineer">
                         インフラエンジニア
                    </label>
               </div>
               <label class="py-4" for="work">前職</label>
               <input type="text" name="work" id="work" placeholder="保険の営業" class="form-control" value="<?php if (isset($_SESSION['work'])){echo htmlspecialchars($_SESSION['work']);} ?>">
               <label class="py-4" for="question">ご質問（100字以内）</label>
               <textarea class="form-control" name="question" id="question" cols="50" rows="5" placeholder="Shimoningの名前の由来は？"><?php if (isset($_SESSION['question'])){echo htmlspecialchars($_SESSION['question']);} ?></textarea>
               <label class="py-4" for="annual_income">ご希望の年収（単位:万円）</label>
               <input type="text" name="annual_income" pattern="^[1-9][0-9]*$" id="annual_income" placeholder="100" class="form-control" value="<?php if (isset($_SESSION['annual_income'])){echo htmlspecialchars($_SESSION['annual_income']);} ?>">
               <input type="submit" name="submit" value="エントリーする" class="btn btn-success px-5 my-4">
          </form>
     </div>
     <footer class='text-center'>&copy; shimoning.com</footer>
</body>
</html>
