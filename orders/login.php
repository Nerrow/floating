<?php
error_reporting(E_ERROR);

require_once('Config.php');
require_once('Auth.php');

$dbh = new PDO('sqlite:orders.db');

$config = new PHPAuth\Config($dbh);
$auth = new PHPAuth\Auth($dbh, $config);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$login = trim($_POST['login']);
	$password = trim($_POST['password']);
	$remember = intval($_POST['remember']);
	
	$result = $auth->login($login, $password, $remember);
	
	if(!$result['error']) {
		header('Location: index.php');
		exit();
	}
}

if($auth->isLogged()) {
	header('Location: index.php');
	exit();
}
?>

<!DOCTYPE html>
<html lang="ru-RU">
	<head>
		<title>База данных C4B - Вход</title>
		
		<meta charset="utf-8">
		<meta name="format-detection" content="telephone=no">
		<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">	
		<meta name="keywords" content="">
		<meta name="description" content="">
		
		<link href="favicon-32x32.png" rel="icon" type="image/png" sizes="32x32">
		<link href="favicon-16x16.png" rel="icon" type="image/png" sizes="16x16">
		
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link href="css/common.css" rel="stylesheet" type="text/css">
	</head>
	<body class="login-page">
		<section class="h-100">
			<div class="container h-100">
				<div class="row justify-content-md-center h-100">
					<div class="card-wrapper">
						<div class="brand">
							<img src="images/logo.png">
						</div>
						<div class="card fat">
							<div class="card-body">
								<h4 class="card-title">Вход</h4>
								<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
								 
									<div class="form-group">
										<label for="login">Логин</label>
										<input id="login" type="text" class="form-control<?php echo ($result['login'] ? ' is-invalid': '');?>" name="login" value="<?php echo $login;?>" required<?php echo ($_SERVER['REQUEST_METHOD'] != 'POST' ? ' autofocus' : '');?>>
										<?php if($result['login']):?>
										<div class="invalid-feedback">
											<?php echo $result['login'];?>
										</div>
										<?php endif;?>
									</div>

									<div class="form-group">
										<label for="password">Пароль
											<a href="forgot.php" class="float-right">
												Забыли пароль?
											</a>
										</label>
										<input id="password" type="password" class="form-control<?php echo ($result['password'] ? ' is-invalid': '');?>" name="password" value="<?php echo $password;?>" required data-eye>
										<?php if($result['password']):?>
										<div class="invalid-feedback">
											<?php echo $result['password'];?>
										</div>
										<?php endif;?>
									</div>

									<div class="form-group">
										<label>
											<input type="checkbox" name="remember" value="1"<?php echo ($remember ? ' checked' : '');?>> Запомнить меня
										</label>
									</div>

									<div class="form-group no-margin">
										<button type="submit" class="btn btn-dark btn-block">
											Войти
										</button>
									</div>
									<input type="hidden" class="form-control<?php echo ($result['message'] ? ' is-invalid': '');?>">
									<?php if($result['message']):?>
									<div class="invalid-feedback">
										<?php echo $result['message'];?>
									</div>
									<?php endif;?>
								</form>
							</div>
						</div>
						<div class="footer">
							&copy; 2016-<?php echo date('Y');?> Client 4 Business
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<script src="js/jquery-3.3.1.min.js" type="text/javascript"></script>
		<script src="js/popper.min.js" type="text/javascript"></script>
		<script src="js/bootstrap.min.js" type="text/javascript"></script>
		<script src="js/common.js" type="text/javascript"></script>
	</body>
</html>
