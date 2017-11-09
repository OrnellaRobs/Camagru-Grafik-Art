<?php
if (!empty($_POST) && !empty($_POST['username']) && !empty($_POST['password']))
{
	require_once 'inc/db.php';
	require_once 'inc/functions.php';
	$req = $pdo->prepare('SELECT * from User WHERE (username = :username OR email = :username) AND confirmation_at IS NOT NULL');
	$req->execute([
		'username' => $_POST['username']
	]);
	$user = $req->fetch();
	if ($user && password_verify($_POST['password'], $user->password))
	{
		session_start();
		$_SESSION['auth'] = $user;
		$_SESSION['success'] = "Vous êtes maintenant connecté";
		header('Location: account.php');
		exit();
	}
	else
		$_SESSION['danger'] = "Identifiant/Email ou mot de passe inccorects";

}

?>

<?php require 'inc/header.php'; ?>

<h1>SE CONNECTER</h1>

<form class="form-login" action="" method="post">
	<input class="input-login" type="text" name="username" title="identifiant" placeholder="Identifiant ou Email"/><br/>
	<input class="input-login" type="password" name="password" title="password" placeholder="Mot de Passe"/><br/>
	<input class="login-submit" type="submit" value="Se Connecter"><br/>
	<a href="forget.php">(J'ai oublié mon mot de passe)</>
	<br/><label>
		<input type="checkbox" name="remember" value="1"/>Se souvenir de moi</a>
	</label>
</form>
<?php require 'inc/footer.php'; ?>