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
		if (!$this->permissions->UtilisateurAPermission('article_creer')) {
			header('Location: /accueil');
			exit;
		}

		$peutPublier = $this->permissions->UtilisateurAPermission('article_publier');

		echo $this->twig->render('creer_article.twig', [
			'articlesNav' => $this->articleModel->getArticlesNav(),
			'peutPublier' => $peutPublier
		]);
	}

	public function traitementCreation()
	{
		// 1. Vérification des permissions
		if (!$this->permissions->UtilisateurAPermission('article_creer')) {
			header('Location: /accueil');
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$titre = trim($_POST['titre']);
			$contenu = $_POST['contenu']; // Le Markdown brut
			$statut = $_POST['statut'];

			if (!$this->permissions->UtilisateurAPermission('article_publier')) {
				$statut = 'Brouillon';
			}
			
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

	public function editer()
	{
		// 1. Sécurité
		$session = SessionManager::getInstance();
		if (!$session->get('user_id')) { header('Location: /connexion'); exit; }

		// 2. Récupération
		if (!isset($_GET['id'])) { header('Location: /accueil'); exit; }
		$article = $this->articleModel->getArticle($_GET['id']);

		if (!$article) { header('Location: /accueil'); exit; }

		// 3. Vérification Propriétaire
		// On compare le nom d'utilisateur de l'article avec celui de la session
		if ($article['nom_utilisateur'] !== $session->get('username')) {
			// Si ce n'est pas mon article, retour au dashboard
			header('Location: /connexion'); 
			exit;
		}

		$peutPublier = $this->permissions->UtilisateurAPermission('article_publier');

		echo $this->twig->render('creer_article.twig', [
			'is_edit' => true,      // Active le mode édition
			'data' => $article,     // Pré-remplit les champs
			'article_id' => $article['id'],
			'articlesNav' => $this->articleModel->getArticlesNav(),
			'peutPublier' => $peutPublier
		]);
	}

	public function traitementEdition()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$id = $_POST['article_id'];
			$titre = trim($_POST['titre']);
			$contenu = $_POST['contenu'];
			$statut = $_POST['statut'];
			
			// RÈGLE MÉTIER : Si pas de permission publier, on force le statut Brouillon
			if (!$this->permissions->UtilisateurAPermission('article_publier')) {
				$statut = 'Brouillon';
			}

			// Régénération du slug (au cas où le titre change)
			$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $titre))));

			$result = $this->articleModel->miseAJourArticle($id, $titre, $slug, $contenu, $statut);

			if ($result === true) {
				header('Location: /connexion');
				exit;
			} else {
				// Erreur : on réaffiche le formulaire
				$peutPublier = $this->permissions->UtilisateurAPermission('article_publier');
				echo $this->twig->render('creer_article.twig', [
					'is_edit' => true,
					'error' => is_string($result) ? $result : "Erreur lors de la mise à jour",
					'data' => $_POST,
					'article_id' => $id,
					'articlesNav' => $this->articleModel->getArticlesNav(),
					'peutPublier' => $peutPublier
				]);
			}
		}
	}
}
