<?php
/* inclure l'autoloader */
require_once '../vendor/autoload.php';
require_once '../app/controlleur/ArticleControlleur.php';
require_once '../app/model/listeArticles.php';
/* templates chargés à partir du système de fichiers (répertoire vue) */
$loader = new Twig\Loader\FilesystemLoader('../app/views');
/* options : prod = cache dans le répertoire cache, dev = pas de cache */
$options_prod = array('cache' => 'cache', 'autoescape' => true);
$options_dev = array('cache' => false, 'autoescape' => true);
/* stocker la configuration */
$twig = new Twig\Environment($loader);

$controller = new ArticleControlleur($twig);
if (isset($_GET['id'])) {
	$controller->article($_GET['id']);
} else {
	$controller->index();
}
