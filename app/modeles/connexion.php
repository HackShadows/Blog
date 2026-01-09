<?php
require_once 'SessionManager.php';
require_once 'Logger.php';
$session = SessionManager::getInstance();
$logger = Logger::getInstance();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM Utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $session->set('user_id', $user['id']);
            $session->set('username', $user['username']);
            $logger->log("Connexion réussie pour {$user['username']}");
            exit;
        } else {
            $logger->log("Échec de connexion pour $username");
            $error = "Identifiants invalides.";
        }
    } catch (PDOException $e) {
        $error = "Erreur de base de données.";
        $logger->log("Erreur PDO : " . $e->getMessage());
    }
}
?>