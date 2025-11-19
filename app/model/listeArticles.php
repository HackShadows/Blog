<?php // app/models/listeArticles.php
class ListeArticle
{
	private $db;
	public function __construct()
	{
		$this->db = new PDO(
			'mysql:host=localhost;dbname=blog_db',
			'root',
			''
		);
	}
	public function getArticle($articleId)
	{
		$query = $this->db->prepare("SELECT * FROM article WHERE id = :id");
		$query->bindParam(':id', $articleId);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getResumeArticles()
	{
		$query = $this->db->prepare("SELECT id, titre, contenu_resume FROM article");
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
}
