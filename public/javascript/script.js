function contactForm() {
	return {
		errors: {},
		nom: '',
		email: '',
		message: '',
		modalOpen: false,
		init() { },
		validateEmail(email) {
			const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return regex.test(email);
		},
		validate() {
			this.errors = {};
			this.modalOpen = false;

			if (!this.nom) {
				this.errors.nom = "Le nom est requis.";
			}

			if (!this.email) {
				this.errors.email = "L'email est requis.";
			} else if (!this.validateEmail(this.email)) {
				this.errors.email = "L'email est invalide.";
			}

			if (!this.message.trim()) {
				this.errors.message = "Le message est requis.";
			}

			if (Object.keys(this.errors).length === 0) {
				this.nom = "";
				this.email = "";
				this.message = "";
				this.modalOpen = true;
			}
		},
	};
}

// Ciblage des éléments
let darkModeIcon = document.getElementById('darkModeIcon');

// Récupération de la préférence de thème dans le stockage local
let darkMode = localStorage.getItem('darkMode') === 'true';

// Fonction qui met à jour la couleur de fond
function updateBg() {
	darkModeIcon.src = darkMode ? '/images/soleil.png' : '/images/lune.png';
	darkModeIcon.alt = darkMode ? 'Soleil' : 'Lune';
	document.documentElement.setAttribute('data-bs-theme', darkMode ? 'dark' : 'light');
	
	if (darkMode) {
		document.documentElement.style.setProperty('--c', '20');
		document.documentElement.style.setProperty('--font-color', 'rgb(220, 220, 220)');
	} else {
		document.documentElement.style.setProperty('--c', '220');
		document.documentElement.style.setProperty('--font-color', 'rgb(20, 20, 20)');
	}
}

function toggleDyslexic() {
    document.body.classList.toggle('dyslexic-mode');
    localStorage.setItem('dyslexic', document.body.classList.contains('dyslexic-mode'));
}

// Événement de clic sur l'icône
darkModeIcon.addEventListener('click', function() {
	darkMode = !darkMode;
	// Sauvegarde de la préférence dans le stockage local
	localStorage.setItem('darkMode', darkMode);
	updateBg();
});


// Au chargement :
if (localStorage.getItem('dyslexic') === 'true') document.body.classList.add('dyslexic-mode');
updateBg();