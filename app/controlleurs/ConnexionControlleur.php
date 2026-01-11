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

    public function dashboard() {
        $session = SessionManager::getInstance();
        $this->logs->log("dashboard");
        $userId = $session->get('user_id');

        $rolesPermissions = ['admin_acces' => $this->permissions->UtilisateurAPermission('admin_acces'),
            'article_creer' => $this->permissions->UtilisateurAPermission('article_creer'),
            'article_editer_tous' => $this->permissions->UtilisateurAPermission('article_editer_tous'),
            'article_publier' => $this->permissions->UtilisateurAPermission('article_publier'),
            'article_supprimer' => $this->permissions->UtilisateurAPermission('article_supprimer'),
            'commentaire_gerer' => $this->permissions->UtilisateurAPermission('commentaire_gerer'),
            'tag_gerer' => $this->permissions->UtilisateurAPermission('tag_gerer'),
            'utilisateur_gerer' => $this->permissions->UtilisateurAPermission('utilisateur_gerer')];
        $dashboardModel = new Dashboard();
        $listeUtilisateurs = [];
        $tousLesRoles = [];
        $tousLesArticles = [];
        $listeCommentaires = [];
        $filtreCommentaires = $_POST['filter_comm'] ?? 'En attente';
        $mesArticles = $dashboardModel->getArticlesAuteur($userId);
        $listeTags = [];

        if ($rolesPermissions['utilisateur_gerer']) {
            $listeUtilisateurs = $dashboardModel->getUtilisateursAvecRoles();
            $tousLesRoles = $dashboardModel->getTousLesRoles();
        }
        if ($rolesPermissions['article_editer_tous']) {
            $tousLesArticles = $dashboardModel->getTousLesArticles();
        }
        if ($rolesPermissions['tag_gerer']) {
            $listeTags = $dashboardModel->getTagsAvecCount();
        }


        if ($rolesPermissions['commentaire_gerer']) {
            if ($filtreCommentaires !== 'tous' and $filtreCommentaires !== 'En attente' and $filtreCommentaires !== 'Rejeté' and $filtreCommentaires !== 'Approuvé') {
                $filtreCommentaires = 'tous';
            }
            $listeCommentaires = $dashboardModel->getCommentaires($filtreCommentaires);
        }
        echo $this->twig->render('dashboard.twig', [
            'userId' => $userId,
            'permissions' => $rolesPermissions,
            'listeUtilisateurs' => $listeUtilisateurs,
            'tousLesRoles' => $tousLesRoles,
            'articlesNav' => $this->articleModel->getArticlesNav(),
            'tousLesArticles' => $tousLesArticles,
			'mesArticles' => $mesArticles,
            'listeCommentaires' => $listeCommentaires,
            'listeTags' => $listeTags,
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
            $result = $connexionModel->EnregistrerUtilisateur($username, $email, $password);

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
        if ($this->permissions->UtilisateurAPermission('utilisateur_gerer')) {

            // 2. Vérification des données POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
                $userId = $_POST['user_id'];
                $roles = $_POST['roles'] ?? []; // Tableau vide si aucun rôle coché

                $this->logs->log("majRoles: Mise à jour demandée pour User ID " . $userId);

                $dashboardModel = new Dashboard();
                $result = $dashboardModel->miseAJourRole($userId, $roles);

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
        if ($this->permissions->UtilisateurAPermission('utilisateur_gerer')) {
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
                    $dashboardModel->supprimerUtilisateur($userId);
                }
            }
        }
        header('Location: /connexion');
        exit;
    }

    public function changerStatutArticle() {
        if ($this->permissions->UtilisateurAPermission('article_editer_tous')) {
            if (isset($_POST['article_id']) && isset($_POST['statut'])) {
                $dashboardModel = new Dashboard();
                $dashboardModel->miseAJourStatutArticle($_POST['article_id'], $_POST['statut']);
            }
        }
        header('Location: /connexion');
        exit;
    }

    public function supprimerArticle() {
        if ($this->permissions->UtilisateurAPermission('article_supprimer')) {
            if (isset($_POST['article_id'])) {
                $dashboardModel = new Dashboard();
                $dashboardModel->supprimerArticle($_POST['article_id']);
            }
        }
        header('Location: /connexion');
        exit;
    }

    public function changerStatutCommentaire() {
        if ($this->permissions->UtilisateurAPermission('commentaire_gerer')) {
            if (isset($_POST['comment_id']) && isset($_POST['statut'])) {
                $dashboardModel = new Dashboard();
                $dashboardModel->miseAJourStatutCommentaire($_POST['comment_id'], $_POST['statut']);
            }
        }
        header('Location: /connexion');
        exit;
    }

    public function supprimerCommentaire() {
        if ($this->permissions->UtilisateurAPermission('commentaire_gerer')) {
            if (isset($_POST['comment_id'])) {
                $dashboardModel = new Dashboard();
                $dashboardModel->supprimerCommentaire($_POST['comment_id']);
            }
        }
        header('Location: /connexion');
        exit;
    }

}