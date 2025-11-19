CREATE DATABASE IF NOT EXISTS blog_db; USE blog_db;

CREATE TABLE article ( 
	id INT AUTO_INCREMENT PRIMARY KEY, 
	titre VARCHAR(128) NOT NULL UNIQUE, 
	date_publication DATE, 
	contenu TEXT NOT NULL, 
	contenu_resume TEXT NOT NULL );

CREATE TABLE contact ( 
	id INT AUTO_INCREMENT PRIMARY KEY, 
	nom VARCHAR(32) NOT NULL, 
	email VARCHAR(64), 
	contenu_message TEXT NOT NULL );

-- Insertion de 2 articles dans la base 
INSERT INTO article (titre, date_publication, contenu, contenu_resume) 
VALUES (
	"L'essor de l'IA générative dans le développement logiciel", 
	"2025-10-03", 
	" < p > L'intelligence artificielle générative, autrefois cantonnée à la recherche, est désormais au cœur des outils de développement modernes. 
	Des solutions comme GitHub Copilot, ChatGPT ou encore Tabnine transforment la manière dont les développeurs conçoivent et maintiennent leurs projets. < br > < br > 
	Ces IA sont capables d'assister la rédaction de code, de suggérer des corrections, d'expliquer des algorithmes complexes ou encore d'automatiser la documentation technique. 
	En parallèle, elles réduisent considérablement le temps consacré aux tâches répétitives, permettant aux ingénieurs de se concentrer sur l'architecture et la logique métier. < br > < br > 
	Toutefois, cette évolution soulève aussi des défis : 
	< ul > < li > < b > Qualité < /b > : les suggestions ne sont pas toujours optimales et nécessitent une validation humaine. < /li > 
	< li > < b > Fiabilité < /b > : les suggestions générées ne sont pas toujours optimisées ou sécurisées. < /li > 
	< li > < b > Éthique < /b > : des questions persistent sur les droits liés au code produit à partir de modèles entraînés sur des dépôts publics. < /li > 
	< li > < b > Formation < /b > : les développeurs doivent apprendre à collaborer efficacement avec ces nouveaux outils, sans en devenir dépendants. < /li > < /ul > 
	L'avenir semble pointer vers une collaboration étroite entre humains et IA, où l'IA agit comme copilote et non comme substitut. 
	À mesure que les modèles progressent, on peut s'attendre à une démocratisation encore plus poussée du développement logiciel, accessible à un plus grand nombre de personnes. < /p > ", 
	"L'IA générative révolutionne le développement logiciel en devenant un copilote qui accélère le code tout en soulevant des enjeux de fiabilité et d'éthique.");

INSERT INTO article (titre, date_publication, contenu, contenu_resume) 
VALUES (
	"La cybersécurité face à l'explosion des objets connectés", 
	"2025-10-03", 
	" < p > Avec l'essor de l'Internet des Objets (IoT), des milliards d'appareils — montres connectées, caméras, capteurs industriels — sont aujourd'hui reliés au réseau mondial. 
	Cette révolution technologique apporte de nombreux bénéfices : suivi médical à distance, villes intelligentes, automatisation domestique… < br > < br > 
	Cependant, elle ouvre aussi la porte à de nouvelles menaces. Chaque objet connecté représente un point d'entrée potentiel pour des cyberattaques. 
	Failles logicielles, mots de passe par défaut non changés et absence de mises à jour de sécurité rendent ces appareils particulièrement vulnérables. < br > < br > 
	Les experts recommandent plusieurs mesures : 
	< ul > < li > Renforcer la conception sécurisée dès la fabrication des appareils. < /li > 
	< li > Éduquer les utilisateurs à modifier leurs paramètres par défaut. < /li > 
	< li > Développer des standards internationaux pour sécuriser l'écosystème IoT. < /li > < /ul > 
	En somme, si l'IoT promet un monde plus efficace et connecté, il impose aussi une vigilance accrue en matière de cybersécurité. < /p > ", 
	"L'explosion des objets connectés offre des opportunités majeures mais accroît les risques de cyberattaques, nécessitant une sécurité renforcée dès la conception.");