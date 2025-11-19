<?php 
class ArticleControlleur
{
	private $articleModel;
	private $twig;
	public function __construct(Twig\Environment $twig)
	{
		$this->articleModel = new ListeArticle();
		$this->twig = $twig;
	}
	public function index()
	{
		$resumes = $this->articleModel->getResumeArticles();
		echo $this->twig->render('accueil.twig', ['resumes' => $resumes]);
	}
	public function article($articleId)
	{
		$articles = $this->articleModel->getArticle($articleId);
		$commentaires = $this->articleModel->getCommentaire($articleId);

		if (empty($articles)) {
            throw new Exception("L'article avec l'id $articleId n'existe pas.");
        }

		$article = $articles[0]; 
		echo $this->twig->render('article.twig', ['article' => $article, 'commentaires' => $commentaires]);
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
