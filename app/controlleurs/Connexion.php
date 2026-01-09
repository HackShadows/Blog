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
		echo $this->twig->render('connexion.twig', []);
	}
}