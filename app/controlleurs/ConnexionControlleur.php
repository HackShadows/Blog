<?php // app/controlleurs/ConnexionControlleur.php
class ConnexionControlleur
{
	private $twig;
	private $logs;
	private $permissions;

	public function __construct(Twig\Environment $twig)
	{
		$this->logs = Logger::getInstance();
		$this->twig = $twig;
		$this->permissions = new Permissions();
	}

	public function index()
	{
		$this->logs->log("connexion");
		echo $this->twig->render('connexion.twig', []);
	}

	public function dashboard($userEmail)
	{
        $session = SessionManager::getInstance();
		$this->logs->log("dashboard");
        $userId = $session->get('user_id');
		$roles = ['Administrateur' => $this->permissions->hasRole('Administrateur'), 'Contributeur' => $this->permissions->hasRole('Contributeur'), 'Éditeur' => $this->permissions->hasRole('Éditeur')];
        $listeUtilisateurs = null;
        if ($roles['Administrateur']){
            $listeUtilisateurs = (new Utilisateurs())->getUtilisateurs();
        }
        if ($roles['Contributeur']){

        }
        if ($roles['Editeur']){

        }
        echo $this->twig->render('dashboard.twig', ['userId' => $userId, 'roles' => $roles, 'listeUtilisateurs' => $listeUtilisateurs]);

    }

    public function changerUtilisateur(){
        $logger = Logger::getInstance();
        $logger->log("toggleUser-entree dans fct");
        if (isset($_POST['id'])) {
            $logger->log("toggleUser-entree dans if");
            $userId = intval($_POST['id']);

            $dashboardModel = new Dashboard();
            $dashboardModel->changerStatutUtilisateur($userId);
        }
    }
}