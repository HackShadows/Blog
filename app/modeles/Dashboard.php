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
}