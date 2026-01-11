<?php // app/modeles/Permissions.php
//todo faire des fct booleenes qui verifient l'indice des perms de l'user dans session
class Permissions
{
	private $session;
	private $logger;
	private $db;

	public function __construct()
	{
		$this->db = Database::getInstance()->getConnection();
		$this->session = SessionManager::getInstance();
		$this->logger = Logger::getInstance();
	}
	
	public function UtilisateurAPermission($permissionName){
		try {
			$query = $this->db->prepare("SELECT DISTINCT p.id, u.nom_utilisateur 
										FROM Permissions p 
										JOIN Role_Permission rp ON p.id = rp.permission_id
										JOIN Role_User ru ON ru.role_id = rp.role_id 
										JOIN Utilisateurs u ON u.id = ru.user_id
										WHERE p.nom_permission = ? and u.id = ?;");
			$query->execute([$permissionName, $this->session->get('user_id')]);
			$boolean = $query->fetch(PDO::FETCH_ASSOC);
			if ($boolean) {
				return true;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			$error = "Erreur de base de données.";
			$this->logger->log("Erreur PDO : " . $e->getMessage());
			return false;
		}
	}

	public function UtilisateurARole($roleName){
		try {
			$query = $this->db->prepare("SELECT DISTINCT u.nom_utilisateur FROM Roles r
										JOIN Role_User ru ON r.id = ru.role_id
										JOIN Utilisateurs u ON u.id = ru.user_id
										WHERE r.nom_role = ? and u.id = ?;");
			$query->execute([$roleName, $this->session->get('user_id')]);
			$boolean = $query->fetch(PDO::FETCH_ASSOC);
			if ($boolean) {
				return true;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			$error = "Erreur de base de données.";
			$this->logger->log("Erreur PDO : " . $e->getMessage());
			return false;
		}
	}
}
