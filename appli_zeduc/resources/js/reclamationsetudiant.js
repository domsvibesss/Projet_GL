import { lireReclamations, creerReclamation, STATUTS } from './reclamations.js'

const liste = document.getElementById('liste-reclamations')
const modele = document.getElementById('modele-reclamation')
const messageVide = document.getElementById('reclamations-vide')

const reduireMouvements = window.matchMedia('(prefers-reduced-motion: reduce)').matches

function formaterDate(iso) {
    const d = new Date(iso)
    if (isNaN(d)) return '—'
    const p = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}, ${p(d.getHours())}:${p(d.getMinutes())}`
}

function traiterArrivee() {
    const parametres = new URLSearchParams(window.location.search)
    const idCommande = parametres.get('commande')
    if (!idCommande) return null

    creerReclamation(idCommande)
    window.history.replaceState(null, '', window.location.pathname)
    return idCommande
}

function afficherReclamations(idMisEnAvant) {
    const reclamations = lireReclamations().sort((a, b) => new Date(b.creeLe) - new Date(a.creeLe))

    liste.replaceChildren()
    messageVide.classList.toggle('hidden', reclamations.length > 0)

    reclamations.forEach((reclamation, index) => {
        const ligne = modele.content.firstElementChild.cloneNode(true)
        const el = (role) => ligne.querySelector(`[data-role="${role}"]`)
        const statut = STATUTS[reclamation.statut] || STATUTS.attente

        el('id').textContent = `#${reclamation.commandeId}`
        el('plat').textContent = reclamation.plat
        el('date').textContent = formaterDate(reclamation.date)
        el('prix').textContent = reclamation.prix
        el('statut').textContent = statut.libelle
        el('statut').classList.add(statut.bordure, statut.texte)

        if (!reduireMouvements) {
            ligne.classList.add('article-entree')
            ligne.style.setProperty('--i', index)

            if (idMisEnAvant && String(reclamation.commandeId) === String(idMisEnAvant)) {
                ligne.classList.add('reclamation-nouvelle')
            }
        }

        liste.append(ligne)
    })
}

const idArrivee = traiterArrivee()
afficherReclamations(idArrivee)