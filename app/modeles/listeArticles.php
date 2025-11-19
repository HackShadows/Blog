<?php // app/modeles/listeArticles.php
class ListeArticle
{
	private $db;
	public function __construct()
	{
		$this->db = new PDO(
			'mysql:host=127.0.0.1;dbname=blog_db',
			'root',
			''
		);
	}
	public function getArticle($articleId)
	{
		$query = $this->db->prepare("SELECT a.titre, a.contenu, a.image_une, a.date_creation, a.date_mise_a_jour, u.nom_utilisateur FROM Articles a 
                                            JOIN Utilisateurs u ON a.utilisateur_id = u.id WHERE id = :id");
		$query->bindParam(':id', $articleId);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

    public function getCommentaire($articleId)
    {
        $query = $this->db->prepare("SELECT c.nom_auteur, c.email FROM commenatire c 
                                            JOIN Utilisateurs u ON a.utilisateur_id = u.id WHERE id = :id");
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
