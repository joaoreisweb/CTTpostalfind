<?php
header("Content-Type: text/html; charset=utf-8",true);

require_once('class.ctt.php');

if (!empty($_POST)){

	$findctt = new CTTpostalfind();
	//$findctt->setDistrito("Setúbal");
	//$findctt->setConcelho("Porto");
	//$findctt->setLocal("barreiro");
	$findctt->setCodpos($_POST["cp"]);

	//$findctt->setRua("tomé");
	//$findctt->setEp("");
	//$findctt->setIDlo("45135");
	//$findctt->setApartado("10202");

	// search ( original | simple | all , output json )
	$result = $findctt->search('all', false);

	echo "<br>---- result ----<br><br>";
	var_dump($result);
	echo "<br><hr><br>".json_encode($result);
	echo "<br><br>-- end result --<hr>";

}

?>

<html>
<head>
	<title></title>
</head>
<body>
<form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
        CP: <input type="text" name="cp">
        <input type="submit">
    </form>
<p><?php echo (isset($result['localidade']['designacao'])) ? $result['localidade']['designacao'] : ''; ?></p>
</body>
</html>
