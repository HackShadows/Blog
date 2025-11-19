<?php 
require_once 'SessionManager.php'; 
require_once 'Logger.php'; 
  
$session = SessionManager::getInstance(); 
$logger = Logger::getInstance(); 
  
if (!$session->get('user_id')) { 
    header('Location: index.php'); 
    exit; 
} 
  
$username = $session->get('username'); 
$logger->log("Accès au dashboard par $username"); 
?> 
  
<h1>Bienvenue, <?= htmlspecialchars($username) ?> !</h1> 
<p>Ceci est une page protégée.</p> 
<a href="logout.php">Se déconnecter</a>