

<?php
require_once __DIR__ . "/../modeles/permissions.php";
class Connexion
{
	private $twig;
	private $logs;

	public function __construct(Twig\Environment $twig)
	{
		$this->logs = Logger::getInstance();
		$this->twig = $twig;
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
//		$user = (new Dashboard())->getUser($userEmail);
        $userId = $session->get('user_id');
		$roles = ['Administrateur' => hasRole('Administrateur'), 'Contributeur' => hasRole('Contributeur'), 'Éditeur' => hasRole('Éditeur')];
		print_r($userId);
		print_r($roles);
		echo $this->twig->render('dashboard.twig', ['userId' => $userId, 'roles' => $roles]);
	}
}