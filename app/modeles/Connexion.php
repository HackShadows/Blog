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

	public function registerUser($username, $email, $password) {
		try {
			// 1. Vérifier si l'utilisateur existe déjà
			$check = $this->db->prepare("SELECT id FROM Utilisateurs WHERE email = ? OR nom_utilisateur = ?");
			$check->execute([$email, $username]);
			if ($check->fetch()) {
				return "Cet email ou ce nom d'utilisateur est déjà pris.";
			}

			// 2. Hachage du mot de passe (Sécurité)
			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

			// 3. Démarrer une transaction (Pour insérer User + Rôle atomiquement)
			$this->db->beginTransaction();

			// Insertion Utilisateur
			$query = $this->db->prepare("INSERT INTO Utilisateurs (nom_utilisateur, email, mot_de_passe, est_actif, date_inscription) VALUES (?, ?, ?, 1, NOW())");
			$query->execute([$username, $email, $hashedPassword]);
			
			// Récupération de l'ID créé automatiquement
			$userId = $this->db->lastInsertId();

			// Attribution du rôle par défaut (3 = Contributeur)
			$queryRole = $this->db->prepare("INSERT INTO Role_User (role_id, user_id) VALUES (3, ?)");
			$queryRole->execute([$userId]);

			$this->db->commit();
			$this->logger->log("Nouveau compte créé : $username");
			return true;

		} catch (PDOException $e) {
			$this->db->rollBack();
			$this->logger->log("Erreur inscription : " . $e->getMessage());
			return "Erreur technique lors de l'inscription.";
		}
	}

}
?>