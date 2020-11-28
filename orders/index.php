<?php
error_reporting(E_ERROR);

require_once('Config.php');
require_once('Auth.php');

$dbh = new PDO('sqlite:orders.db');

$config = new PHPAuth\Config($dbh);
$auth = new PHPAuth\Auth($dbh, $config);

if(!$auth->isLogged()) {
	$auth->register('client4business@gmail.com', 'admin', 'admin');
	header('Location: login.php');
	exit();
}

$user = $auth->getCurrentUser();

?>

<!DOCTYPE html>
<html lang="ru-RU">
	<head>
		<title>Заявки Client 4 Business</title>
		
		<meta charset="utf-8">
		<!--<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">-->
		<meta name="format-detection" content="telephone=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="keywords" content="">
		<meta name="description" content="">
		
		<link href="favicon-32x32.png" rel="icon" type="image/png" sizes="32x32">
		<link href="favicon-16x16.png" rel="icon" type="image/png" sizes="16x16">
		
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link href="css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css">	
		<link href="css/ui.jqgrid.css" rel="stylesheet" type="text/css">
		
		<link href="css/common.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<header>
			<div class="navbar navbar-dark bg-dark box-shadow">
				<div class="container d-flex justify-content-between">
					<a href="" class="navbar-brand d-flex align-items-center">
						<strong>Заявки Client 4 Business</strong>
					</a>
					<div class="user-panel">
						<?php echo $user['email']; ?>
						<a class="btn btn-outline-light" href="logout.php">Выход</a>
					</div>
				</div>
			</div>
		</header>	
		<main role="main">
			<div class="album py-5 bg-light">
				<div class="container-fluid">
					<h4 class="jumbotron-heading text-center">ЗАКАЗЫ</h4>
					<table id="list"></table>
					<div id="pager"></div>
				</div>
			</div>
		</main>
		<footer class="text-muted">
			<div class="container text-center">
				<p>&copy; 2016-<?php echo date('Y');?> Client 4 Business</p>
			</div>
		</footer>
		<script src="js/jquery-3.3.1.min.js" type="text/javascript"></script>
		<script src="js/popper.min.js" type="text/javascript"></script>
		<script src="js/bootstrap.min.js" type="text/javascript"></script>
		<script src="js/i18n/grid.locale-ru.js" type="text/javascript"></script>
		<script src="js/jquery.jqGrid.min.js" type="text/javascript"></script>
		<script src="js/common.js" type="text/javascript"></script>
		<script src="js/grid.js" type="text/javascript"></script>
		
	</body>	
</html>