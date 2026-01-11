<?php // app/controlleurs/ArticleControlleur.php
class ArticleControlleur
{
	private $articleModel;
	private $twig;
	private $logs;
	private $permissions;
	private $session;

	public function __construct(Twig\Environment $twig)
	{
		$this->articleModel = new Articles();
		$this->logs = Logger::getInstance();
        $this->session = SessionManager::getInstance();
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

		if (empty($article)) {
			$this->logs->log("Erreur id inconnu : L'article avec l'id $articleId n'existe pas.");
            throw new InvalidArgumentException("L'article avec l'id $articleId n'existe pas.");
        }

		if ($article['statut'] === 'Brouillon') {
            $currentUser = $this->session->get('username');
            $authorUser = $article['nom_utilisateur'];
            
            $canPublish = $this->permissions->UtilisateurAPermission('article_publier');

            if ($currentUser !== $authorUser && !$canPublish) {
                $this->logs->log("Accès refusé : $currentUser a tenté de lire le brouillon $articleId");
                header('Location: /accueil');
                exit;
            }
        }

		$commentaires = $this->articleModel->getCommentaire($articleId);

		$this->logs->log("ouvrir article id=" . strval($article["id"]));
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
		if (!$this->permissions->UtilisateurAPermission('article_creer')) {
			header('Location: /accueil');
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$titre = trim($_POST['titre']);
			$contenu = $_POST['contenu'];
			$statut = $_POST['statut'];

			$image = $this->uploadImage();

			if (!$this->permissions->UtilisateurAPermission('article_publier')) {
				$statut = 'Brouillon';
			}
			
			$userId = $this->session->get('user_id');
			$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $titre))));

			if (!empty($titre) && !empty($contenu) && !empty($userId)) {
				$result = $this->articleModel->creerArticle($titre, $slug, $contenu, $userId, $statut, $image);
				
				if ($result === true) {
					header('Location: /accueil');
					exit;
				} else {
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
		if (!$this->session->get('user_id')) { header('Location: /connexion'); exit; }

		// 2. Récupération
		if (!isset($_GET['id'])) { header('Location: /accueil'); exit; }
		$article = $this->articleModel->getArticle($_GET['id']);

		if (!$article) { header('Location: /accueil'); exit; }

		// 3. Vérification Propriétaire
		// On compare le nom d'utilisateur de l'article avec celui de la session
		if ($article['nom_utilisateur'] !== $this->session->get('username')) {
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

			$image = $this->uploadImage();
			if (!$image) {
				$image = $_POST['current_image'] ?? null;
			}
			
			// RÈGLE MÉTIER : Si pas de permission publier, on force le statut Brouillon
			if (!$this->permissions->UtilisateurAPermission('article_publier')) {
				$statut = 'Brouillon';
			}

			// Régénération du slug (au cas où le titre change)
			$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $titre))));

			$result = $this->articleModel->miseAJourArticle($id, $titre, $slug, $contenu, $statut, $image);

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

	private function uploadImage() {
		if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
			$tmpName = $_FILES['image']['tmp_name'];
			$name = basename($_FILES['image']['name']);
			$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
			$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

			if (in_array($ext, $allowed)) {
				$newName = uniqid('img_', true) . '.' . $ext;
				$uploadDir = __DIR__ . '/../../public/uploads/';
				
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir, 0755, true);
				}

				if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
					return $newName;
				}
			}
		}
		return null;
	}
}
