<!DOCTYPE php>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <title>検索機能(入力)</title>
</head>
<body>
名前を入力して検索すると、番号、名前、希望ポジション、質問が表示されます。
<!-- action属性は、form要素の中の属性の1つで、フォームに入力された情報の送信先を指定する。今回はsearch-out.php -->
<form action="search-output.php" method="post">
<input type="text" name="keyword">
<input type="submit" value="検索する">
</form>
</body>
</html>
