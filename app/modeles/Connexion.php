<?php // app/modeles/Connexion.php
class Connexion
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

	public function logIn() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$username = $_POST['email'] ?? '';
			$password = $_POST['password'] ?? '';
			try {
				$query = $this->db->prepare("SELECT * FROM Utilisateurs WHERE email = ? AND est_actif = 1");
				$query->execute([$username]);
				$user = $query->fetch(PDO::FETCH_ASSOC);
				if ($user && password_verify($password, $user['mot_de_passe'])) {
					$this->session->set('user_id', $user['id']);
					$this->session->set('username', $user['nom_utilisateur']);
					print_r($this->session->get('user_id'));
					$this->logger->log("Connexion réussie pour {$user['nom_utilisateur']}");
					return true;
				} else {
					$this->logger->log("Échec de connexion pour $username");
					return false;
				}
			} catch (PDOException $e) {
				$error = "Erreur de base de données.";
				$this->logger->log("Erreur PDO : " . $e->getMessage());
				return false;
			}
		}
	}

    public function logOut() {
        $this->logger->log("Deconnexion de ".$this->session->get('username'));
        $this->session->set('user_id', null);
        $this->session->set('username', null);
    }

	public function createUser() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$email = $_POST['email'] ?? '';
			$username = $_POST['username'] ?? '';
			$password = $_POST['password'] ?? '';
			try {
				$id = $this->db->prepare("SELECT MAX(id)+1 FROM Utilisateurs");
				$id->execute();
				$query = $this->db->prepare("INSERT INTO Utilisateurs (id, email, nom_utilisateur, mot_de_passe, est_actif, date_inscription) VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
				$query->execute([$id, $email, $username, $password]);
				$query = $this->db->prepare("INSERT INTO Role_User (role_id, user_id) VALUES (3, ?)");
				$query->execute([$id]);
				$this->logger->log("Création de  compte réussie pour {$username}");
				exit;
			} catch (PDOException $e) {
				$this->logger->log("Erreur PDO : " . $e->getMessage());
			}
		}
	}

}
?>