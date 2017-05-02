<?php


function connect_db(){
	global $connection;
	$host="localhost";
	$user="test";
	$pass="t3st3r123";
	$db="test";
	$connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa ühendust mootoriga- ".mysqli_error());
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
}

function logi(){
	// siia on vaja funktsionaalsust (13. nädalal)
	global $connection;
	if (!empty($_SESSION['user'])){
		header("Location: ?page=loomad");
	}
	if ($_SERVER['REQUEST_METHOD']=='GET'){
		include_once('views/login.html');
	}
	if (empty($_POST['user']) || empty($_POST['pass'])){
		$errors[]= "Kasutajanimi/Parool puudu!";
		include_once('views/login.html');
	}
	if ($_SERVER['REQUEST_METHOD']=="POST"){
		if (isset($_POST['user']) && isset($_POST['pass'])){
			$kasutaja =  mysqli_real_escape_string($connection,htmlspecialchars($_POST['user']));
			$parool = mysqli_real_escape_string($connection,htmlspecialchars($_POST['pass']));
			$sql = "SELECT * FROM 12103979_kylastajad WHERE username='$kasutaja' AND passw=SHA1('$parool')";
			$result = mysqli_query($connection, $sql);
			if (mysqli_num_rows($result) >= 1){
				$_SESSION['user'] = mysqli_fetch_assoc($result);
				header("Location: ?page=loomad");
			}else{
				$errors[]= "Kasutajat ei eksisteeri!";
				include_once('views/login.html');
			}
		}
	}
	
}

function logout(){
	$_SESSION=array();
	session_destroy();
	header("Location: ?");
}

function kuva_puurid(){
	global $connection;
	// siia on vaja funktsionaalsust
	if (empty($_SESSION['user'])){
		header("Location: ?page=login");
	}
	$puurid=array();
	$puuri_p2ring=mysqli_query($connection, "SELECT DISTINCT(puur) FROM 12103979_loomaaed ORDER BY puur ASC");
	while($puuri_nr = mysqli_fetch_assoc($puuri_p2ring)){
		$loom_puuris=$puuri_nr["puur"];
		$looma_p2ring=mysqli_query($connection, "SELECT * FROM 12103979_loomaaed WHERE puur=$loom_puuris");
		while($loomarida = mysqli_fetch_assoc($looma_p2ring)){
			$puurid[$puuri_nr["puur"]][]=$loomarida;
		}
	}
	include_once('views/puurid.html');
}

function lisa(){
	// siia on vaja funktsionaalsust (13. nädalal)
	global $connection;
	if (empty($_SESSION['user'])){
		header("Location: ?page=login");
	}
	if ($_SERVER['REQUEST_METHOD']=='GET'){
		include_once('views/loomavorm.html');
	}
	if ($_SERVER['REQUEST_METHOD']=='POST'){
		if (empty($_POST['nimi'])){
			$errors[]= "Nimi puudub!";
		}
		if (empty($_POST['vanus'])){
			$errors[]= "Vanus puudub!";
		}
		if (empty($_POST['puur'])){
			$errors[]= "Puuri number puudub!";
		}
		if (empty($_FILES["liik"]["name"])){
			$errors[]= "Näopilt puudub!";
		}
		$nimi = mysqli_real_escape_string($connection, $_POST["nimi"]);
		$vanus = mysqli_real_escape_string($connection, $_POST["vanus"]);
		$puur = mysqli_real_escape_string($connection, $_POST["puur"]);
		$liik = mysqli_real_escape_string($connection, "pildid/".$_FILES["liik"]["name"]);
		if (empty($errors)){
			echo "Erroreid ei ole!!";
			$sql = "INSERT INTO 12103979_loomaaed  (nimi, vanus, puur, liik) VALUES ('$nimi', $vanus, $puur, '$liik')";
			$result = mysqli_query($connection, $sql);
			print_r ($sql);
			if (mysqli_insert_id($connection) > 0){
				echo "Lisamine õnnestus";
				header("Location: ?page=lisa");
			} else {
				echo "Ei saanud lisada!";
			}
		}
	}
	include_once('views/loomavorm.html');
}

function upload($name){
	$allowedExts = array("jpg", "jpeg", "gif", "png");
	$allowedTypes = array("image/gif", "image/jpeg", "image/png","image/pjpeg");
	$extension = end(explode(".", $_FILES[$name]["name"]));

	if ( in_array($_FILES[$name]["type"], $allowedTypes)
		&& ($_FILES[$name]["size"] < 100000)
		&& in_array($extension, $allowedExts)) {
    // fail õiget tüüpi ja suurusega
		if ($_FILES[$name]["error"] > 0) {
			$_SESSION['notices'][]= "Return Code: " . $_FILES[$name]["error"];
			return "";
		} else {
      // vigu ei ole
			if (file_exists("pildid/" . $_FILES[$name]["name"])) {
        // fail olemas ära uuesti lae, tagasta failinimi
				$_SESSION['notices'][]= $_FILES[$name]["name"] . " juba eksisteerib. ";
				return "pildid/" .$_FILES[$name]["name"];
			} else {
        // kõik ok, aseta pilt
				move_uploaded_file($_FILES[$name]["tmp_name"], "pildid/" . $_FILES[$name]["name"]);
				return "pildid/" .$_FILES[$name]["name"];
			}
		}
	} else {
		return "";
	}
}

?>