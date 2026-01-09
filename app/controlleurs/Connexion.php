

<?php 
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
        logIn();
		echo $this->twig->render('connexion.twig', []);
	}

	public function dashboard($userEmail)
	{
		$this->logs->log("dashboard");
		$user = (new Dashboard())->getUser($userEmail);
		echo $this->twig->render('dashboard.twig', ['user' => $user]);
	}
}