<?php
	session_start();
	foreach($_SESSION as $key => $val) {
		unset($_SESSION[$key]);
	}
	header("Location:./index.php");
?>