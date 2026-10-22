import { lireCommandes } from './commandes.js'

const liste = document.getElementById('liste-commandes')
const modele = document.getElementById('modele-commande')
const messageVide = document.getElementById('historique-vide')

function formaterDate(iso) {
    const d = new Date(iso)
    if (isNaN(d)) return '—'
    const p = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}, ${p(d.getHours())}:${p(d.getMinutes())}`
}

function afficherCommandes() {
    const commandes = lireCommandes().sort((a, b) => new Date(b.date) - new Date(a.date))

    liste.replaceChildren()
    messageVide.classList.toggle('hidden', commandes.length > 0)

    commandes.forEach((commande) => {
        const ligne = modele.content.firstElementChild.cloneNode(true)
        const el = (role) => ligne.querySelector(`[data-role="${role}"]`)

        el('id').textContent = `#${commande.id}`
        el('plat').textContent = commande.plat
        el('date').textContent = formaterDate(commande.date)
        el('prix').textContent = commande.prix
        el('reclamer').href = `/pages/reclamations.html?commande=${encodeURIComponent(commande.id)}`

        liste.append(ligne)
    })
}

afficherCommandes()