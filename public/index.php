<?php
/* inclure l'autoloader */
require_once '../vendor/autoload.php';
/* inclure les controlleurs */
require_once '../app/controlleurs/ArticleControlleur.php';
require_once '../app/controlleurs/ConnexionControlleur.php';
require_once '../app/controlleurs/CommentaireControlleur.php';
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
$CommentaireControlleur = new CommentaireControlleur();
$connexion = new Connexion();
$session = SessionManager::getInstance();
$twig->addGlobal('session', $session);
$logger = Logger::getInstance();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$logger->log($uri);

switch ($uri) {
    case '/':
    case '/accueil':
        $ArticleControlleur->index(null);
        break;
    case '/connexion':
        $userId = $session->get('user_id');
        $logger->log("ID Session actuel : " . $userId);
        if (!empty($userId)) {
            $logger->log("Utilisateur déjà connecté -> Redirection Dashboard");
            $userEmail = $session->get('email') ?? '';
            $ConnexionControlleur->dashboard();
        } elseif (!empty($_POST)) {
            $logger->log("Tentative de connexion (POST)");
            if ($connexion->logIn()) {
                $logger->log("Connexion réussie pour " . $_POST['email']);
                $ConnexionControlleur->dashboard();
            } else {
                $logger->log("Echec connexion");
                echo 'Mauvais email/mot de passe';
                $ConnexionControlleur->index();
            }
        } else {
            $logger->log("Affichage formulaire connexion");
            $ConnexionControlleur->index();
        }
        break;
    case '/changerUtilisateur':
        $ConnexionControlleur->changerUtilisateur();
        $ConnexionControlleur->dashboard();
        break;
    case '/posterCommentaire':
        $CommentaireControlleur->posterCommentaire();
        break;
    case '/inscription':
        $ConnexionControlleur->inscription();
        break;
    case '/traitementInscription':
        $ConnexionControlleur->traitementInscription();
        break;
    case '/creer-article':
        $ArticleControlleur->creer();
        break;
    case '/traitementCreation':
        $ArticleControlleur->traitementCreation();
        break;
    case '/editer-article':
        $ArticleControlleur->editer();
        break;
    case '/traitementEdition':
        $ArticleControlleur->traitementEdition();
        break;
    case '/deconnexion':
        $connexion->logOut();
        $ArticleControlleur->index(null);
        break;
    case '/majRoles':
        $ConnexionControlleur->majRoles();
        break;
    case '/supprimerUtilisateur':
        $ConnexionControlleur->supprimerUtilisateur();
        break;
    case '/changerStatutArticle':
        $ConnexionControlleur->changerStatutArticle();
        break;
    case '/supprimerArticle':
        $ConnexionControlleur->supprimerArticle();
        break;
    case '/changerStatutCommentaire':
        $ConnexionControlleur->changerStatutCommentaire();
        break;
    case '/supprimerCommentaire':
        $ConnexionControlleur->supprimerCommentaire();
        break;

    default:
        if (isset($_GET['id'])) {
            try {
                $ArticleControlleur->article($_GET['id']);
            } catch (InvalidArgumentException $e) {
                $ArticleControlleur->index($e->getMessage());
            }
        } else {
            http_response_code(404);
            echo "Page non trouvée";
        }
        break;
}