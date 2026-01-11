<?php // app/controlleurs/ArticleControlleur.php
class ArticleControlleur
{
	private $articleModel;
	private $twig;
	private $logs;
	private $permissions;

	public function __construct(Twig\Environment $twig)
	{
		$this->articleModel = new Articles();
		$this->logs = Logger::getInstance();
		$this->twig = $twig;
		$this->permissions = new Permissions();
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

	public function creer()
	{
		if (!$this->permissions->hasPermission('article_creer')) {
			header('Location: /accueil');
			exit;
		}

		// On passe 'url' pour le menu
		echo $this->twig->render('creer_article.twig', [
			'articlesNav' => $this->articleModel->getArticlesNav()
		]);
	}

	public function traitementCreation()
	{
		// 1. Vérification des permissions
		if (!$this->permissions->hasPermission('article_creer')) {
			header('Location: /accueil');
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$titre = trim($_POST['titre']);
			$contenu = $_POST['contenu']; // Le Markdown brut
			$statut = $_POST['statut'];
			
			$session = SessionManager::getInstance();
			$userId = $session->get('user_id');

			// Génération du Slug (Titre -> titre-de-l-article)
			// On remplace les accents et caractères spéciaux
			$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $titre))));

			if (!empty($titre) && !empty($contenu) && !empty($userId)) {
				$result = $this->articleModel->creerArticle($titre, $slug, $contenu, $userId, $statut);
				
				if ($result === true) {
					header('Location: /accueil'); // Ou vers le dashboard
					exit;
				} else {
					// Erreur (ex: slug dupliqué)
					echo $this->twig->render('creer_article.twig', [
						'error' => is_string($result) ? $result : "Erreur lors de la création.",
						'data' => $_POST,
						'articlesNav' => $this->articleModel->getArticlesNav()
					]);
				}
			}
		}
	}
}
