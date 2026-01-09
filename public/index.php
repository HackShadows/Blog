<?php
/* inclure l'autoloader */
require_once '../vendor/autoload.php';
require_once '../app/controlleurs/ArticleControlleur.php';
require_once '../app/controlleurs/Connexion.php';
require_once '../app/modeles/listeArticles.php';
require_once '../app/modeles/Logger.php';
/* templates chargés à partir du système de fichiers (répertoire vue) */
$loader = new Twig\Loader\FilesystemLoader('../app/vues');
/* options : prod = cache dans le répertoire cache, dev = pas de cache */
$options_prod = array('cache' => 'cache', 'autoescape' => true);
$options_dev = array('cache' => false, 'autoescape' => true);
/* stocker la configuration */
$twig = new Twig\Environment($loader);

$controller = new ArticleControlleur($twig);
if (isset($_GET['id'])) {
	try {
		$controller->article($_GET['id']);
	} catch (InvalidArgumentException $e) {
		$controller->index($e->getMessage());
	}
} else {
	$uri = $_SERVER['REQUEST_URI'];
	Logger::getInstance()->log($uri);
	switch ($uri) {
		case '/':
		case '/accueil':
			$controller->index(null);
			break;
		case '/connexion':
			(new Connexion($twig))->index();
			break;
		default:
			http_response_code(404);
			break;
	}
}
