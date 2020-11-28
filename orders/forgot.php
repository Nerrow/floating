<?php
error_reporting(E_ERROR);

require_once('Config.php');
require_once('Auth.php');

$dbh = new PDO('sqlite:orders.db');

$config = new PHPAuth\Config($dbh);
$auth = new PHPAuth\Auth($dbh, $config);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	$email = trim($_POST['email']);
	$password = $auth->getRandomKey(8);
	
	$result = $auth->restorePassword($email, $password);
	
	if(!$result['error']) {
		header('Location: forgot.php?forgot=success');
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
		<title>Orders.LPcopier - Восстановление пароля</title>
		
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
				<div class="row justify-content-md-center align-items-center h-100">
					<div class="card-wrapper">
						<div class="brand">
							<img src="images/logo.png">
						</div>
						<div class="card fat">
							<div class="card-body">
								<h4 class="card-title">Восстановление пароля</h4>
								<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
								 
									<div class="form-group">
										<label for="email">E-Mail*</label>
										<input id="email" type="email" class="form-control<?php echo ($result['email'] ? ' is-invalid': '');?>" name="email" value="<?php echo $email;?>" required autofocus>
										<?php if($result['email']):?>
										<div class="invalid-feedback">
											<?php echo $result['email'];?>
										</div>
										<?php endif;?>
										<div class="form-text text-muted">
											Нажмите &laquo;Восстановить пароль&raquo; и мы отправим новый пароль на Ваш email
										</div>
									</div>

									<div class="form-group no-margin">
										<button type="submit" class="btn btn-dark btn-block">
											Восстановить пароль
										</button>
									</div>
									
									<div class="margin-top20 text-center">
										Вспомнили пароль? <a href="login.php">Вход</a>
									</div>
								</form>
							</div>
						</div>
						<div class="footer">
							&copy; 2016-<?php echo date('Y');?> Orders.LPcopier
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<!--noindex-->
		<div class="modal fade" id="forgot-success" tabindex="-1" role="dialog" aria-labelledby="forgot-success-label" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="forgot-success-label">Ваш пароль успешно восстановлен!</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
						<div class="modal-body">
							<p>На ваш E-Mail выслан новый пароль для входа в систему.</p>
						</div>
					<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">Закрыть</button>
					</div>
				</div>
			</div>
		</div>
		<!--/noindex-->
		
		<script src="js/jquery-3.3.1.min.js" type="text/javascript"></script>
		<script src="js/popper.min.js" type="text/javascript"></script>
		<script src="js/bootstrap.min.js" type="text/javascript"></script>
		<script src="js/common.js" type="text/javascript"></script>
	</body>
</html>
