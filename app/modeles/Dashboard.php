<?php // app/modeles/Dashboard.php
class Dashboard
{
	private $db;
	
	public function __construct()
	{
		$this->db = Database::getInstance()->getConnection();
	}

	public function getUser($userEmail)
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

    public function getAllRoles(){
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

    public function updateRoles($userId, $rolesIds) {
        $logger = Logger::getInstance();
        $logger->log("entree dans updateRoles");
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

    // app/modeles/Dashboard.php

    public function deleteUser($userId) {
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

    public function getAllArticlesWithAuthors() {
        $query = $this->db->prepare("SELECT a.*, u.nom_utilisateur 
                FROM Articles a 
                JOIN Utilisateurs u ON a.utilisateur_id = u.id 
                ORDER BY a.date_creation DESC");
        $query->execute();
        $articles = $query->fetchAll(PDO::FETCH_ASSOC);
        return $articles;
    }

    public function updateArticleStatus($id, $newStatus) {
        $allowed = ['Brouillon', 'Publié', 'Archivé'];
        if (!in_array($newStatus, $allowed)) return false;

        $query = $this->db->prepare("UPDATE Articles SET statut = ? WHERE id = ?");
        $query->execute([$newStatus, $id]);
        return true;
    }

    public function deleteArticle($id) {
        $query = $this->db->prepare("DELETE FROM Articles WHERE id = ?");
        $query->execute([$id]);
        return true;
    }

}