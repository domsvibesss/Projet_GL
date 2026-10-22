const CLE_COMPTE = 'compte-miam-miam'

const formulaire = document.getElementById('formulaire')
const erreur = document.getElementById('form-erreur')
const nom = document.getElementById('username')
const email = document.getElementById('email')
const telephone = document.getElementById('telephone')
const motDePasse = document.getElementById('password')

function afficherErreur(texte, champ) {
    erreur.textContent = texte
    erreur.classList.remove('hidden')
    champ.focus()
}

formulaire.addEventListener('submit', (e) => {
    e.preventDefault()
    erreur.classList.add('hidden')

    const valeurNom = nom.value.trim()
    const valeurEmail = email.value.trim()
    const chiffres = telephone.value.replace(/\D/g, '').replace(/^237/, '')

    if (valeurNom.length < 2) {
        afficherErreur('Entrez votre nom d’utilisateur.', nom)
        return
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valeurEmail)) {
        afficherErreur('Entrez une adresse e-mail valide.', email)
        return
    }

    if (!/^6\d{8}$/.test(chiffres)) {
        afficherErreur('Entrez un numéro de téléphone valide (9 chiffres).', telephone)
        return
    }

    if (motDePasse.value.length < 8) {
        afficherErreur('Le mot de passe doit contenir au moins 8 caractères.', motDePasse)
        return
    }

    try {
        localStorage.setItem(
            CLE_COMPTE,
            JSON.stringify({ nom: valeurNom, email: valeurEmail, telephone: chiffres })
        )
    } catch {}

    window.location.href = '/pages/connexion.html'
})