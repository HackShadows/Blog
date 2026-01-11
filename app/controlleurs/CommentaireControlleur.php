<?php // app/controlleurs/CommentaireControlleur.php
class CommentaireControlleur 
{
    private $articleModel;

    public function __construct() {
        $this->articleModel = new Articles();
    }

    public function posterCommentaire() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $articleId = filter_input(INPUT_POST, 'article_id', FILTER_SANITIZE_NUMBER_INT);
            $contenu = htmlspecialchars($_POST['contenu']);
            
            // Gestion utilisateur connecté ou visiteur
            $session = SessionManager::getInstance();
            if ($session->get('username')) {
                $nom = $session->get('username');
                $email = $session->get('email');
            } else {
                $inputNom = trim($_POST['nom_auteur']);
                $nom = !empty($inputNom) ? htmlspecialchars($inputNom) : 'Anonyme';
                $email = htmlspecialchars($_POST['email_auteur']);
            }

            if ($articleId && $contenu && $email) {
                $article = $this->articleModel->getArticle($articleId);
                
                if ($article && $article['statut'] === 'Publié') {
                    $this->articleModel->ajouterCommentaire($articleId, $nom, $email, $contenu);
                }
            }
            
            // Redirection vers l'article
            header("Location: /article?id=" . $articleId);
            exit;
        }
    }
}