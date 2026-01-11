<?php // app/controlleurs/CommentaireControlleur.php
class CommentaireControlleur 
{
    private $articleModel;

    public function __construct() {
        $this->articleModel = new Articles();
    }

    public function posterCommentaire() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $articleId = $_POST['article_id'];
            $contenu = htmlspecialchars($_POST['contenu']);
            
            // Gestion utilisateur connecté ou visiteur
            $session = SessionManager::getInstance();
            if ($session->get('username')) {
                $nom = $session->get('username');
                $email = $session->get('email');
            } else {
                $nom = htmlspecialchars($_POST['nom_auteur']);
                $email = htmlspecialchars($_POST['email_auteur']);
            }

            $this->articleModel->ajouterCommentaire($articleId, $nom, $email, $contenu);
            
            // Redirection vers l'article
            header("Location: /article?id=" . $articleId);
            exit;
        }
    }
}