<?php // app/modeles/Dashboard.php
class Dashboard
{
	private $db;
	
	public function __construct()
	{
		$this->db = Database::getInstance()->getConnection();
	}

	public function getUtilisateur($userEmail)
	{
		$query = $this->db->prepare("SELECT id, nom_utilisateur, email FROM Utilisateurs WHERE email = :email");
		$query->bindParam(':email', $userEmail);
		$query->execute();
        $answer = $query->fetchAll(PDO::FETCH_ASSOC);
		if (empty($answer)) return $answer;
		return $answer[0];
	}

    public function changerStatutUtilisateur($utilisateurId){
        $query = $this->db->prepare("UPDATE Utilisateurs SET est_actif = 1-est_actif WHERE id = ?" );
        return $query->execute([$utilisateurId]);
    }

    public function getTousLesRoles(){
        $query = $this->db->prepare("SELECT * FROM Roles");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUtilisateursAvecRoles() {
        $users = $this->db->query("SELECT * FROM Utilisateurs")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as &$user) {
            $query = $this->db->prepare("
                SELECT r.id, r.nom_role, r.description 
                FROM Roles r 
                JOIN Role_User ru ON r.id = ru.role_id 
                WHERE ru.user_id = ?
            ");
            $query->execute([$user['id']]);
            $user['roles'] = $query->fetchAll(PDO::FETCH_ASSOC); // Ajoute le tableau des rôles
        }

        return $users;
    }

    public function miseAJourRole($userId, $rolesIds) {
        $logger = Logger::getInstance();
        $logger->log("entree dans miseAJourRole");
        try {
            $this->db->beginTransaction();
            $del = $this->db->prepare("DELETE FROM Role_User WHERE user_id = ?");
            $del->execute([$userId]);
            $logger->log("id a modifier".$userId);
            $logger->log("roles ids ".$rolesIds);
            if (!empty($rolesIds) && is_array($rolesIds)) {
                $logger->log("entree dans for");
                $insert = $this->db->prepare("INSERT INTO Role_User (user_id, role_id) VALUES (?, ?)");
                foreach ($rolesIds as $roleId) {
                    $insert->execute([$userId, $roleId]);
                    $logger->log("ajout d'un role " . $roleId);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();

            $logger->log("erreur de bdd pendant update role");
            return false;
        }
    }

    public function getArticlesAuteur($userId) {
		$query = $this->db->prepare("SELECT * FROM Articles WHERE utilisateur_id = ? ORDER BY date_creation DESC");
		$query->execute([$userId]);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

    public function supprimerUtilisateur($userId) {
        try {
            $this->db->beginTransaction();

            $queryArticles = $this->db->prepare("DELETE FROM Articles WHERE utilisateur_id = ?");
            $queryArticles->execute([$userId]);

            $queryUser = $this->db->prepare("DELETE FROM Utilisateurs WHERE id = ?");
            $queryUser->execute([$userId]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();

            Logger::getInstance()->log("Erreur suppression user $userId : " . $e->getMessage());
            return false;
        }
    }

    public function getTousLesArticles() {
        $query = $this->db->prepare("SELECT a.*, u.nom_utilisateur 
                FROM Articles a 
                JOIN Utilisateurs u ON a.utilisateur_id = u.id 
                ORDER BY a.date_creation DESC");
        $query->execute();
        $articles = $query->fetchAll(PDO::FETCH_ASSOC);
        return $articles;
    }

    public function MiseAJourArticleStatus($id, $newStatus) {
        $allowed = ['Brouillon', 'Publié', 'Archivé'];
        if (!in_array($newStatus, $allowed)) return false;

        $query = $this->db->prepare("UPDATE Articles SET statut = ? WHERE id = ?");
        $query->execute([$newStatus, $id]);
        return true;
    }

    public function supprimerArticle($id) {
        $query = $this->db->prepare("DELETE FROM Articles WHERE id = ?");
        $query->execute([$id]);
        return true;
    }

    public function getCommentaires($filtre) {
        $sql = "SELECT c.*, a.titre AS titre_article, a.slug 
                FROM Commentaires c 
                JOIN Articles a ON c.article_id = a.id";

        if ($filtre === 'En attente') {
            $sql .= " WHERE c.statut = 'En attente'";
        }
        if ($filtre === 'Rejeté') {
            $sql .= " WHERE c.statut = 'Rejeté'";
        }
        if ($filtre === 'Approuvé') {
            $sql .= " WHERE c.statut = 'Approuvé'";
        }

        $sql .= " ORDER BY c.date_commentaire DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function miseAJourStatutCommentaire($id, $status) {
        $query = $this->db->prepare("UPDATE Commentaires SET statut = ? WHERE id = ?");
        $query->execute([$status, $id]);
        return true;
    }

    public function supprimerCommentaire($id) {
        $query = $this->db->prepare("DELETE FROM Commentaires WHERE id = ?");
        $query->execute([$id]);
        return true;
    }

    public function getTagsAvecCount() {
        $sql = "SELECT t.id, t.nom_tag, t.slug, COUNT(at.article_id) as nombre_articles 
                FROM Tags t 
                LEFT JOIN Article_Tag at ON t.id = at.tag_id 
                GROUP BY t.id 
                ORDER BY nombre_articles DESC, t.nom_tag ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentairesEnAttente() {
        $sql = "SELECT c.id, c.contenu, c.date_commentaire, c.nom_auteur, a.titre 
                FROM Commentaires c 
                JOIN Articles a ON c.article_id = a.id
                WHERE c.statut = 'En attente' 
                ORDER BY c.date_commentaire DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArticlesBrouillon() {
        $sql = "SELECT a.id, a.titre, a.date_creation, u.nom_utilisateur 
                FROM Articles a 
                JOIN Utilisateurs u ON a.utilisateur_id = u.id
                WHERE a.statut = 'Brouillon'
                ORDER BY a.date_creation DESC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}