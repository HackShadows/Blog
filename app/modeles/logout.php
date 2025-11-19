<?php 
require_once 'SessionManager.php'; 
require_once 'Logger.php'; 
  
$session = SessionManager::getInstance(); 
$logger = Logger::getInstance(); 
  
$username = $session->get('username') ?? 'inconnu'; 
$session->destroy(); 
$logger->log("Déconnexion de $username"); 
  
header('Location: index.php'); 
exit;