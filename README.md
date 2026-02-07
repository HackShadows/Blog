# Moteur de Blog MVC (PHP 8)

![Language](https://img.shields.io/badge/Language-PHP_8-777bb4)
![Database](https://img.shields.io/badge/Database-MySQL-00758f)
![Framework](https://img.shields.io/badge/Frontend-Bootstrap_5-7952b3)
![License](https://img.shields.io/badge/License-MIT-lightgrey)

Un système de gestion de contenu pour blog, développé "from scratch" en PHP natif en suivant l'architecture **MVC (Modèle-Vue-Contrôleur)**.
Projet réalisé dans le cadre de l'UE "Programmation Web" (S5, Polytech Lyon).

## Fonctionnalités

**Partie Publique :**
* Consultation des articles et navigation par tags.
* Espace commentaires (avec modération).
* Mode Sombre / Mode Clair (persistant via LocalStorage).
* Mode Dyslexie (police adaptée).

**Partie Administration (Back-office) :**
* **Authentification sécurisée** (Hachage des mots de passe).
* **Gestion des rôles :** Administrateur, Éditeur, Contributeur.
* **CRUD complet** des articles avec éditeur Markdown (EasyMDE).
* **Dashboard :** Gestion des utilisateurs, modération des commentaires, gestion des tags.
* **Système de notifications** pour les tâches en attente (validation de commentaires, etc.).

## Architecture Technique

* **Backend :** PHP 8 (POO stricte).
* **Base de données :** MySQL / MariaDB (PDO, Requêtes préparées).
* **Templating :** Twig.
* **Frontend :** Bootstrap 5, Alpine.js.
* **Design Patterns :** MVC, Singleton.

### Documentation
* [Cahier des Charges](project-files/CahierDesCharges.pdf)
* [Rapport](project-files/Rapport.pdf)

## Structure du Projet

```text
.
├── .gitignore
├── composer.json
├── LICENSE
├── README.md
│
├── app/                        # Cœur de l'application (MVC)
│   ├── controlleurs/
│   ├── modeles/
│   └── vues/
│
├── config/                     # Configuration
│   ├── config.php.example
│   └── database.sql
│
├── project-files/              # Documentation du projet
│
└── public/                     # Racine Web
    ├── index.php
    ├── css/
    ├── javascript/
    └── uploads/
```

## Installation

1. Clonez le dépôt.

2. Installez les dépendances :

```bash
composer install
```

3. Configurez la base de données :

	* Importez le script `config/database.sql` dans votre SGBD.
	* Renommez `config/config.php.example` en `config/config.php` et modifiez les identifiants.

4. Lancez le serveur :

```bash
cd public
php -S localhost:8000
```

## Auteurs
* **Marius CISERANE**
* **Valentin LAPORTE**

## Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.