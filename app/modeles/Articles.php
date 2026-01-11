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

	public function ajouterCommentaire($articleId, $nom, $email, $contenu)
	{
		$query = $this->db->prepare("INSERT INTO Commentaires (article_id, nom_auteur, email_auteur, contenu, statut) VALUES (?, ?, ?, ?, 'En attente')");
		return $query->execute([$articleId, $nom, $email, $contenu]);
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

	public function creerArticle($titre, $slug, $contenu, $userId, $statut)
	{
		try {
			$query = $this->db->prepare("INSERT INTO Articles (titre, slug, contenu, utilisateur_id, statut, date_creation, date_mise_a_jour) 
										VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
			return $query->execute([$titre, $slug, $contenu, $userId, $statut]);
		} catch (PDOException $e) {
			// Gestion des doublons de slug
			if ($e->getCode() == 23000) {
				return "Ce titre existe déjà (le slug est dupliqué).";
			}
			return false;
		}
	}

	public function updateArticle($id, $titre, $slug, $contenu, $statut)
	{
		try {
			$query = $this->db->prepare("UPDATE Articles 
										SET titre = ?, slug = ?, contenu = ?, statut = ?, date_mise_a_jour = NOW() 
										WHERE id = ?");
			return $query->execute([$titre, $slug, $contenu, $statut, $id]);
		} catch (PDOException $e) {
			if ($e->getCode() == 23000) {
				return "Ce titre existe déjà (le slug est dupliqué).";
			}
			return false;
		}
	}

	public function slugExiste($slug) {
		$query = $this->db->prepare("SELECT id FROM Articles WHERE slug = ?");
		$query->execute([$slug]);
		return $query->fetch();
	}
}
