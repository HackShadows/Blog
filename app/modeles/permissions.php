<?php
//todo faire des fct booleenes qui verifient l'indice des perms de l'user dans session
require_once "SessionManager.php";
require_once "Logger.php";
require_once "database.php";
function hasPermission($permissionName){
    $session = SessionManager::getInstance();
    $logger = Logger::getInstance();
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT DISTINCT p.id, u.nom_utilisateur 
									FROM Permissions p 
									JOIN Role_Permission rp ON p.id = rp.role_id
									JOIN Role_User ru ON ru.role_id = rp.role_id 
									JOIN Utilisateurs u ON u.id = ru.user_id
									WHERE p.nom_permission = ? and u.id = ?;");
        $stmt->execute([$permissionName, $session['user_id']]);
        $boolean = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($boolean) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        $error = "Erreur de base de données.";
        $logger->log("Erreur PDO : " . $e->getMessage());
    }
}

function hasRole($roleName){
	$session = SessionManager::getInstance();
	$logger = Logger::getInstance();
	try {
		$db = Database::getInstance()->getConnection();
		$stmt = $db->prepare("SELECT DISTINCT u.nom_utilisateur FROM Roles r
									JOIN Role_User ru ON r.id = ru.role_id
									JOIN Utilisateurs u ON u.id = ru.user_id
									WHERE r.nom_role = ? and u.id = ?;");
		$stmt->execute([$roleName, $session->get('user_id')]);
		$boolean = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($boolean) {
			return true;
		} else {
			return false;
		}
	} catch (PDOException $e) {
		$error = "Erreur de base de données.";
		$logger->log("Erreur PDO : " . $e->getMessage());
	}
}

