import { calculerFidelite, BADGES, POINTS_COMMANDE, POINTS_PARTAGE, partageDisponible, ajouterBonusPartage } from './fidelite.js'

const badgeNom = document.getElementById('fidelite-badge-nom')
const medaille = document.getElementById('fidelite-medaille')
const points = document.getElementById('fidelite-points')
const reste = document.getElementById('fidelite-reste')
const progression = document.getElementById('fidelite-progression')
const barre = document.getElementById('fidelite-barre')
const historique = document.getElementById('fidelite-historique')
const messageVide = document.getElementById('fidelite-vide')
const modele = document.getElementById('modele-gain')

const boutonAvantages = document.getElementById('fidelite-avantages')
const boutonGagner = document.getElementById('fidelite-gagner')
const boutonEchanger = document.getElementById('fidelite-echanger')
const boutonPartager = document.getElementById('fidelite-partager')
const messagePartage = document.getElementById('partage-message')

const modale = document.getElementById('modale-fidelite')
const modaleTitre = document.getElementById('fidelite-modale-titre')
const modaleListe = document.getElementById('fidelite-modale-liste')
const modaleFermer = document.getElementById('fidelite-modale-fermer')
const reduireMouvements = window.matchMedia('(prefers-reduced-motion: reduce)').matches
let totalAffiche = 0

function formaterDate(iso) {
    const d = new Date(iso)
    if (isNaN(d)) return '—'
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function afficherHistorique(gains, animer = false) {
    historique.replaceChildren()
    messageVide.classList.toggle('hidden', gains.length > 0)

    gains.slice(0, 3).forEach((gain, index) => {
        const ligne = modele.content.firstElementChild.cloneNode(true)
        const el = (role) => ligne.querySelector(`[data-role="${role}"]`)

        el('libelle').textContent = gain.libelle
        el('date').textContent = formaterDate(gain.date)
        el('points').textContent = `+${gain.points} pts`

        if (animer && !reduireMouvements) {
            ligne.classList.add('article-entree')
            ligne.style.setProperty('--i', index + 3)
        }

        historique.append(ligne)
    })
}
function animerCompteur(vers) {
    const depuis = totalAffiche
    totalAffiche = vers

    if (reduireMouvements || depuis === vers) {
        points.textContent = vers.toLocaleString('en-US')
        return
    }

    const debut = performance.now()
    const duree = 1200

    function pas(maintenant) {
        const t = Math.min(1, (maintenant - debut) / duree)
        const progres = 1 - Math.pow(1 - t, 3)
        points.textContent = Math.round(depuis + (vers - depuis) * progres).toLocaleString('en-US')
        if (t < 1) requestAnimationFrame(pas)
    }

    requestAnimationFrame(pas)
}

function definirBarre(pourcentage) {
    if (reduireMouvements) {
        barre.style.width = `${pourcentage}%`
        return
    }
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            barre.style.width = `${pourcentage}%`
        })
    })
}

function afficherBadge(donnees) {
    const { total, badge, suivant } = donnees

    badgeNom.textContent = `Badge ${badge.nom}`
    medaille.setAttribute('fill', badge.couleur)
    animerCompteur(total)

    if (suivant) {
        const ecart = suivant.seuil - badge.seuil
        const pourcentage = Math.round(((total - badge.seuil) / ecart) * 100)
        const nom = document.createElement('span')
        nom.className = 'font-semibold'
        nom.textContent = suivant.nom
        reste.replaceChildren(document.createTextNode(`${suivant.seuil - total} points avant `), nom)
        definirBarre(pourcentage)
        progression.setAttribute('aria-valuenow', String(pourcentage))
    } else {
        reste.textContent = 'Niveau maximum atteint'
        definirBarre(100)
        progression.setAttribute('aria-valuenow', '100')
    }
}
function afficherPartage() {
    const disponible = partageDisponible()
    boutonPartager.disabled = !disponible
    if (!disponible && !messagePartage.textContent) {
        messagePartage.textContent = 'Déjà partagé aujourd’hui. Revenez demain !'
        messagePartage.className = 'mt-2 min-h-5 text-sm font-medium text-[#8E8E93]'
    }
}

function actualiser(animer = false) {
    const donnees = calculerFidelite()
    afficherBadge(donnees)
    afficherHistorique(donnees.gains, animer)
    afficherPartage()
}
function ouvrirModale(titre, lignes) {
    modaleTitre.textContent = titre
    modaleListe.replaceChildren()

    lignes.forEach((texte) => {
        const element = document.createElement('li')
        element.className = 'rounded-[12px] bg-[#D9D9D9] px-4 py-3'
        element.textContent = texte
        modaleListe.append(element)
    })

    modale.classList.remove('hidden')
    modale.classList.add('flex')
    modaleFermer.focus()
}

function fermerModale() {
    modale.classList.add('hidden')
    modale.classList.remove('flex')
}

async function partager() {
    const adresse = window.location.origin
    const texte = 'Découvre Mon Miam Miam : des plats généreux et livrés chez toi !'

    try {
        if (navigator.share) {
            await navigator.share({ title: 'Mon Miam Miam', text: texte, url: adresse })
        } else {
            await navigator.clipboard.writeText(`${texte} ${adresse}`)
        }
    } catch (erreur) {
        if (erreur.name === 'AbortError') return
        messagePartage.textContent = 'Le partage a échoué. Réessayez.'
        messagePartage.className = 'mt-2 min-h-5 text-sm font-medium text-red-600'
        return
    }

    if (ajouterBonusPartage()) {
        messagePartage.textContent = navigator.share
            ? `+${POINTS_PARTAGE} points bonus ajoutés !`
            : `Lien copié. +${POINTS_PARTAGE} points bonus ajoutés !`
        messagePartage.className = 'mt-2 min-h-5 text-sm font-medium text-[#188F00]'
        actualiser()
    }
}

boutonAvantages.addEventListener('click', () => {
    const { badge } = calculerFidelite()
    ouvrirModale(`Avantages ${badge.nom}`, badge.avantages)
})

boutonGagner.addEventListener('click', () => {
    ouvrirModale('Comment gagner des points', [
        `${POINTS_COMMANDE} points pour chaque commande effectuée`,
        `${POINTS_PARTAGE} points bonus en partageant Mon Miam Miam à un ami (une fois par jour)`,
        ...BADGES.map((b) => `Badge ${b.nom} à partir de ${b.seuil} points`),
    ])
})

boutonEchanger.addEventListener('click', () => {
    ouvrirModale('Échanger mes récompenses', ['Cette fonctionnalité arrive bientôt.'])
})

boutonPartager.addEventListener('click', partager)
modaleFermer.addEventListener('click', fermerModale)

modale.addEventListener('click', (e) => {
    if (e.target === modale) fermerModale()
})

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fermerModale()
})

actualiser(true)

if (!reduireMouvements) {
    document.querySelectorAll('main section').forEach((bloc, index) => {
        bloc.classList.add('bloc-entree')
        bloc.style.setProperty('--i', index)
    })
    medaille.closest('div').classList.add('medaille-entree')
}