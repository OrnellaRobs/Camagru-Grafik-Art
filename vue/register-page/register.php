<?php
require_once dirname(__FILE__) . '/../../inc/functions.php';
check_session();
check_already_login();
if (!empty($_POST))
{
	$errors = array();
	require_once dirname(__FILE__) . '/../../inc/db.php';
	if (empty($_POST['username']) || !preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username']) || strlen($_POST['username']) < 6) {
		$str = (strlen($_POST['username']) < 6) ? "L'identifiant doit avoir au moins 6 caractères" : "L'identifiant n'est pas valide";
		$errors['username'] = $str;
	}
	else {
		$req = $pdo->prepare('SELECT id FROM User WHERE username = ?');
		$req->execute([$_POST['username']]);
		$user = $req->fetch();
		if ($user)
		{
			$errors['username'] = 'Cet identifiant est déjà pris';
		}
	}
	if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = "L'e-mail n'est pas valide";
	}
	else {
		$req = $pdo->prepare('SELECT id FROM User WHERE email = ?');
		$req->execute([$_POST['email']]);
		$user = $req->fetch();
		if ($user)
		{
			$errors['email'] = 'Cet e-mail est déjà pris';
		}
	}
	if (empty($_POST['password']) || $_POST['password'] != $_POST['password-confirm'] || strlen($_POST['password']) < 4 || !password_check_alphanum($_POST['password'])) {
		$str = (strlen($_POST['password']) < 4) ? "Le mot de passe doit avoir au moins 4 caractères dont des chiffres et des lettres" : "Le mot de passe n'est pas valide";
		$str = (!password_check_alphanum($_POST['password'])) ? "Le mot de passe doit contenir des lettres ainsi que des chiffres" : $str;
		$errors['password'] = $str;
	}
	if (empty($errors)) {
		$req = $pdo->prepare("INSERT INTO User SET name = :name, username = :username, password = :password, email = :email, confirmation_token = :token, mail_comments = :mail_comments");
		$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
		$token = str_random(60);
		$req->execute([
			'name' => $_POST['name'],
			'username' => $_POST['username'],
			'password'=> $password,
			'email' => $_POST['email'],
			'token' => $token,
			'mail_comments' => 1
		]);
		$user_id = $pdo->lastInsertId();
		$entetes =
		'Content-type: text/html; charset=utf-8' . "\r\n" .
		'From: no-reply@camagru.fr' . "\r\n" .
		'Reply-To: no-reply@camagru.fr' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
		$objet = "Confirmation d'Inscription";
		$content = "Afin de finaliser ton inscription, il te suffit de cliquer sur ce lien:\n\nhttp://localhost:8080/Camagru-Grafik-Art/vue/register-page/confirm.php?id=$user_id&token=$token";
		if (mail($_POST['email'], $objet, $content, $entetes))
		{
			$_SESSION['success'] = "Un email de confirmation a été envoyé pour valider le compte";
			header('Location: ../../index.php');
			exit();
		}
		else {
			echo '<script>alert(" Email PAS envoyé")</script>';
		}
	}
}
?>
<?php
require_once dirname(__FILE__) . '/../header/header.php';
require_once dirname(__FILE__) . '/../navbar/navbar.php';
?>

<?php	if (!empty($errors)): ?>
	<div class="danger">
		<p>Le formulaire n'est pas rempli correctement</p>
		<?php foreach($errors as $error):?>
			<li><?=$error;?></li>
		<?php endforeach;?>
	</div>
<?php 	endif; ?>
<div class="title-msg">
	Rejoins-Nous et Inscris-Toi!
	<center><img src="/Camagru-Grafik-Art/images/arrow2.png" width="80px"/></center>
</div>
<div class="register-page-background">
	<div class="register-form">
	<form method="post">
		<input class="input-register" type="text" name="name" placeholder="Nom Complet"/><br/>
		<input class="input-register" type="text" name="username" placeholder="Identifiant"/><br/>
		<input class="input-register" type="password" name="password" placeholder="Mot de Passe" /><br/>
		<input class="input-register" type="password" name="password-confirm" placeholder="Confirmation du mot de passe"/><br/>
		<input class="input-register" type="email" name="email" placeholder="Email"/><br />
		<input class="register-submit" type="submit" name="submit" value="S'Inscrire!">
	</form>
</div>
</div>
<?php require_once dirname(__FILE__) . '/../footer/footer.php';?>
