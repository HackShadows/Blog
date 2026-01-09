<?php
require_once 'SessionManager.php';
require_once 'Logger.php';

function logIn() {
    $session = SessionManager::getInstance();
    $logger = Logger::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM Utilisateurs WHERE email = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password_hash'])) {
                $session->set('user_id', $user['id']);
                $session->set('username', $user['nom_utilisateur']);
                $logger->log("Connexion réussie pour {$user['nom_utilisateur']}");
                exit;
            } else {
                $logger->log("Échec de connexion pour $username");
            }
        } catch (PDOException $e) {
            $error = "Erreur de base de données.";
            $logger->log("Erreur PDO : " . $e->getMessage());
        }
    }
}

function createUser() {
    $logger = Logger::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO Utilisateurs (email, nom_utilisateur, mot_de_passe, est_actif, date_inscription) VALUES (?, ?, ?, 1, CURRENT_TIMESTAMP)");
            $stmt->execute([$email, $username, $password]);
            $logger->log("Création de  compte réussie pour {$username}");
            exit;
        } catch (PDOException $e) {
            $logger->log("Erreur PDO : " . $e->getMessage());
        }
    }
}


?>