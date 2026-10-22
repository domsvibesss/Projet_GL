const CLE_SESSION = 'session-miam-miam'

const formulaire = document.getElementById('formulaire')
const erreur = document.getElementById('form-erreur')
const email = document.getElementById('email')
const motDePasse = document.getElementById('password')

function afficherErreur(texte, champ) {
    erreur.textContent = texte
    erreur.classList.remove('hidden')
    champ.focus()
}

formulaire.addEventListener('submit', (e) => {
    e.preventDefault()
    erreur.classList.add('hidden')

    const valeurEmail = email.value.trim()

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valeurEmail)) {
        afficherErreur('Entrez une adresse e-mail valide.', email)
        return
    }

    if (motDePasse.value.length === 0) {
        afficherErreur('Entrez votre mot de passe.', motDePasse)
        return
    }

    try {
        localStorage.setItem(
            CLE_SESSION,
            JSON.stringify({ email: valeurEmail, connecteLe: new Date().toISOString() })
        )
    } catch {}

    window.location.href = '/index.html'
})