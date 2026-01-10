<?php
/* inclure l'autoloader */
require_once '../vendor/autoload.php';
/* inclure les controlleurs */
require_once '../app/controlleurs/ArticleControlleur.php';
require_once '../app/controlleurs/ConnexionControlleur.php';
/* inclure les modèles */
require_once '../app/modeles/Articles.php';
require_once '../app/modeles/Dashboard.php';
require_once '../app/modeles/Logger.php';
require_once '../app/modeles/Connexion.php';
require_once '../app/modeles/Permissions.php';
require_once '../app/modeles/Utilisateurs.php';
require_once '../app/modeles/Database.php';
require_once '../app/modeles/SessionManager.php';

/* templates chargés à partir du système de fichiers (répertoire vue) */
$loader = new Twig\Loader\FilesystemLoader('../app/vues');
/* options : prod = cache dans le répertoire cache, dev = pas de cache */
$options_prod = array('cache' => 'cache', 'autoescape' => true);
$options_dev = array('cache' => false, 'autoescape' => true);
/* stocker la configuration */
$twig = new Twig\Environment($loader);


$ArticleControlleur = new ArticleControlleur($twig);
$ConnexionControlleur = new ConnexionControlleur($twig);
$connexion = new Connexion();
$session = SessionManager::getInstance();
$logger = Logger::getInstance();

if (isset($_GET['id'])) {
	try {
		$ArticleControlleur->article($_GET['id']);
	} catch (InvalidArgumentException $e) {
		$ArticleControlleur->index($e->getMessage());
	}
} else {
	$uri = $_SERVER['REQUEST_URI'];
	$logger->log($uri);
	switch ($uri) {
		case '/':
		case '/accueil':
			$ArticleControlleur->index(null);
			break;
		case '/connexion':
			if (empty($_POST)) {
				$ConnexionControlleur->index();
			} else {
                if($connexion->logIn()){
                    $ConnexionControlleur->dashboard($_POST["email"]);
                    print_r($session->get('user_id'));
                    $logger->log("L'utilisateur connecté est ".$session->get('user_id'));

                }
                else{
                    echo 'Mauvais email/mot de passe';
                    $ConnexionControlleur->index();
                }
			}
			break;
		default:
			http_response_code(404);
			break;
	}
}
