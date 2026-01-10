<?php

class listeUtilisateurs {
    private $db;
    public function __construct()
    {
        $this->db = new PDO(
            'mysql:host=127.0.0.1;dbname=blog_db',
            'root',
            ''
        );
    }

    public function getUtilisateur($utilisateurId)
    {
        $query = $this->db->prepare("SELECT * FROM Utilisateurs WHERE id = ?" );
        $query->execute([$utilisateurId]);
        $answer = $query->fetchAll(PDO::FETCH_ASSOC);
        if (empty($answer)) return $answer;
        $answer = $answer[0];
        if (!empty($answer) && array_key_exists("contenu", $answer)) {
            $Parsedown = new Parsedown();
            $answer['contenu'] = $Parsedown->text($answer['contenu']);
        }
        return $answer;
    }

    public function getUtilisateurs()
    {
        $query = $this->db->prepare("SELECT * FROM Utilisateurs" );
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}