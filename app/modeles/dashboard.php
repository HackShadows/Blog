<?php 
// require_once 'SessionManager.php'; 
// require_once 'Logger.php'; 
  
// $session = SessionManager::getInstance(); 
// $logger = Logger::getInstance(); 
  
// if (!$session->get('user_id')) { 
//     header('Location: index.php'); 
//     exit; 
// } 
  
// $username = $session->get('username'); 
// $logger->log("Accès au dashboard par $username"); 
?>

<?php // app/modeles/dashboard.php
class Dashboard
{
	private $db;
	public function __construct()
	{
		$this->db = new PDO(
			'mysql:host=127.0.0.1;dbname=blog_db',
			'root',
			''
		);
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
}