<?php // app/controlleurs/ConnexionControlleur.php
class ConnexionControlleur {
    private $twig;
    private $logs;
    private $permissions;
    private $articleModel;

    public function __construct(Twig\Environment $twig) {
        $this->logs = Logger::getInstance();
        $this->twig = $twig;
        $this->permissions = new Permissions();
        $this->articleModel = new Articles();
    }

    public function index() {
        $this->logs->log("connexion");
        echo $this->twig->render('connexion.twig', ['articlesNav' => $this->articleModel->getArticlesNav()]);
    }

    public function dashboard($userEmail) {
        $session = SessionManager::getInstance();
        $this->logs->log("dashboard");
        $userId = $session->get('user_id');

        $rolesPermissions = ['admin_acces' => $this->permissions->hasPermission('admin_acces'),
            'article_creer' => $this->permissions->hasPermission('article_creer'),
            'article_editer_tous' => $this->permissions->hasPermission('article_editer_tous'),
            'article_publier' => $this->permissions->hasPermission('article_publier'),
            'article_supprimer' => $this->permissions->hasPermission('article_supprimer'),
            'commentaire_gerer' => $this->permissions->hasPermission('commentaire_gerer'),
            'tag_gerer' => $this->permissions->hasPermission('tag_gerer'),
            'utilisateur_gerer' => $this->permissions->hasPermission('utilisateur_gerer')];
        $dashboardModel = new Dashboard();
        $listeUtilisateurs = [];
        $tousLesRoles = [];
        $tousLesArticles = [];
        if ($rolesPermissions['utilisateur_gerer']) {
            $listeUtilisateurs = $dashboardModel->getUtilisateursAvecRoles();
            $tousLesRoles = $dashboardModel->getAllRoles();
        }
        if ($rolesPermissions['article_editer_tous']) {
            $tousLesArticles = $dashboardModel->getAllArticlesWithAuthors();
        }

		$mesArticles = $dashboardModel->getArticlesAuteur($userId);

        echo $this->twig->render('dashboard.twig', [
            'userId' => $userId,
            'permissions' => $rolesPermissions,
            'listeUtilisateurs' => $listeUtilisateurs,
            'tousLesRoles' => $tousLesRoles,
            'articlesNav' => $this->articleModel->getArticlesNav(),
            'tousLesArticles' => $tousLesArticles,
			'mesArticles' => $mesArticles
        ]);
    }

    public function inscription() {
        echo $this->twig->render('inscription.twig', [
            'articlesNav' => $this->articleModel->getArticlesNav()
        ]);
    }

    public function traitementInscription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars($_POST['username']);
            $email = htmlspecialchars($_POST['email']);
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if ($password !== $confirm) {
                echo $this->twig->render('inscription.twig', [
                    'error' => "Les mots de passe ne correspondent pas.",
                    'data' => $_POST, // Pour ne pas tout retaper
                    'articlesNav' => $this->articleModel->getArticlesNav()
                ]);
                return;
            }

            $connexionModel = new Connexion();
            $result = $connexionModel->registerUser($username, $email, $password);

            if ($result === true) {
                header('Location: /connexion');
                exit;
            } else {
                echo $this->twig->render('inscription.twig', [
                    'error' => $result,
                    'data' => $_POST,
                    'articlesNav' => $this->articleModel->getArticlesNav()
                ]);
            }
        }
    }

    public function majRoles() {
        // On utilise le logger de la classe
        $this->logs->log("majRoles: Début du traitement");

        // 1. Vérification de la permission (avec l'instance de la classe)
        if ($this->permissions->hasPermission('utilisateur_gerer')) {

            // 2. Vérification des données POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
                $userId = $_POST['user_id'];
                $roles = $_POST['roles'] ?? []; // Tableau vide si aucun rôle coché

                $this->logs->log("majRoles: Mise à jour demandée pour User ID " . $userId);

                $dashboardModel = new Dashboard();
                $result = $dashboardModel->updateRoles($userId, $roles);

                if ($result) {
                    $this->logs->log("majRoles: Succès updateRoles");
                } else {
                    $this->logs->log("majRoles: Erreur retournée par updateRoles");
                }

            } else {
                $this->logs->log("majRoles: Erreur - Pas de POST ou user_id manquant");
            }
        } else {
            $this->logs->log("majRoles: Erreur - Permission 'utilisateur_gerer' refusée");
        }

        // 3. REDIRECTION SYSTÉMATIQUE (Empêche la page blanche)
        // On redirige vers /connexion qui gère intelligemment le retour au dashboard
        header('Location: /connexion');
        exit;
    }

    public function changerUtilisateur() {
        $this->logs->log("toggleUser-entree dans fct");
        if (isset($_POST['id'])) {
            $this->logs->log("toggleUser-entree dans if");
            $userId = intval($_POST['id']);

            $dashboardModel = new Dashboard();
            $dashboardModel->changerStatutUtilisateur($userId);
        }
    }

    public function supprimerUtilisateur() {
        // 1. Vérifier la permission
        $logger = Logger::getInstance();
        $logger->log("supprimerUtilisateur");
        if ($this->permissions->hasPermission('utilisateur_gerer')) {
            $logger->log("supprimerUtilisateur permission");
            // 2. Vérifier le POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
                $userId = intval($_POST['user_id']);
                $logger->log("supprimerUtilisateur : user_id " . $userId);
                // Empêcher de se supprimer soi-même (sécurité basique)
                $session = SessionManager::getInstance();
                if ($userId === $session->get('user_id')) {
                    // On peut ajouter un message flash ici si vous en avez
                } else {
                    $dashboardModel = new Dashboard();
                    $dashboardModel->deleteUser($userId);
                }
            }
        }
        header('Location: /connexion');
        exit;
    }

    public function changerStatutArticle() {
        if ($this->permissions->hasPermission('article_editer_tous')) {
            if (isset($_POST['article_id']) && isset($_POST['statut'])) {
                $dashboardModel = new Dashboard();
                $dashboardModel->updateArticleStatus($_POST['article_id'], $_POST['statut']);
            }
        }
        header('Location: /connexion');
        exit;
    }

    public function supprimerArticle() {
        if ($this->permissions->hasPermission('article_supprimer')) {
            if (isset($_POST['article_id'])) {
                $dashboardModel = new Dashboard();
                $dashboardModel->deleteArticle($_POST['article_id']);
            }
        }
        header('Location: /connexion');
        exit;
    }


}