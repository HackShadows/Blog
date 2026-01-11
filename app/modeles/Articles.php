<?php // app/modeles/Articles.php
class Articles
{
	private $db;
	
	public function __construct()
	{
		$this->db = Database::getInstance()->getConnection();
	}

	public function getArticle($articleId)
	{
		$query = $this->db->prepare("SELECT a.id, a.titre, a.contenu, a.image_une, a.date_creation, a.date_mise_a_jour, u.nom_utilisateur FROM Articles a 
                                            JOIN Utilisateurs u ON a.utilisateur_id = u.id WHERE a.id = :id");
		$query->bindParam(':id', $articleId);
		$query->execute();
        $answer = $query->fetchAll(PDO::FETCH_ASSOC);
		if (empty($answer)) return $answer;
		$answer = $answer[0];
		if (!empty($answer) && array_key_exists("contenu", $answer)) {
			$Parsedown = new Parsedown();
        	$answer['contenu'] = $Parsedown->text($answer['contenu']);
		}
		return $answer;
	}

	public function getArticlesNav()
	{
		$query = $this->db->prepare("SELECT id, slug FROM Articles WHERE statut = 'Publié'
											ORDER BY date_mise_a_jour DESC limit 5");
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

    public function getCommentaire($articleId)
    {
        $query = $this->db->prepare("SELECT c.nom_auteur, c.contenu, c.date_commentaire FROM Commentaires c
                                            JOIN Articles a ON a.id = c.article_id WHERE a.id = :id AND c.statut = 'Approuvé'" );
        $query->bindParam(':id', $articleId);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

	public function getResumeArticles()
	{
		$query = $this->db->prepare("SELECT id, titre, slug, date_mise_a_jour FROM Articles WHERE statut = 'Publié' 
                                            ORDER BY date_mise_a_jour DESC limit 10");
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
}
