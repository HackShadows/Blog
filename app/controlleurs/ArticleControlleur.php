<?php // app/controlleurs/ArticleControlleur.php
class ArticleControlleur
{
	private $articleModel;
	private $twig;
	private $logs;

	public function __construct(Twig\Environment $twig)
	{
		$this->articleModel = new Articles();
		$this->logs = Logger::getInstance();
		$this->twig = $twig;
	}

	public function index($messageErreur)
	{
		$this->logs->log("accueil");
		$resumes = $this->articleModel->getResumeArticles();
		echo $this->twig->render('accueil.twig', ['resumes' => $resumes, 'articlesNav' => $this->articleModel->getArticlesNav(), "erreur" => $messageErreur]);
	}

	public function article($articleId)
	{
		$article = $this->articleModel->getArticle($articleId);
		$commentaires = $this->articleModel->getCommentaire($articleId);

		if (empty($article)) {
			$this->logs->log("Erreur id inconnu : L'article avec l'id $articleId n'existe pas.");
            throw new InvalidArgumentException("L'article avec l'id $articleId n'existe pas.");
        }

		$this->logs->log("ouvrir article id=" . strval($article["id"]));
		// $dico = "[ ";
		// foreach ($article as $key => $value) {
		// 	$dico = $dico . $key . "=" . strval($value) . " ";
		// }
		// $dico = $dico . "]";
		// $this->logs->log($dico);
		echo $this->twig->render('article.twig', ['article' => $article, 'commentaires' => $commentaires, 'articlesNav' => $this->articleModel->getArticlesNav()]);
	}
	// public function addTask($taskName)
	// {
	// 	$this->articleModel->addTask($taskName);
	// 	header('Location: /');
	// }
	// public function deleteTask($taskId)
	// {
	// 	$this->articleModel->deleteTask($taskId);
	// 	header('Location: /');
	// }
}
