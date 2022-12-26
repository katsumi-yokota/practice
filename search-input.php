<!DOCTYPE php>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <title>検索機能(入力)</title>
</head>
<body>
名前を入力して検索すると、番号、名前、希望ポジション、質問が表示されます。
<form action="search-output.php" method="post">
<input type="text" name="keyword">
<input type="submit" value="検索する">
</form>
</body>
</html>
