import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { QUARTIERS, QUARTIER_DEFAUT, CITE_DEFAUT, CENTRE_DOUALA, BORNES_DOUALA } from './quartiers.js'
import { lirePanier, viderPanier } from './panier.js'
import { enregistrerCommande } from './commandes.js'

const LIVRAISON = 200
const EMBALLAGE = 100
const TAUX_PARRAINAGE = 0.01
const CODE_TEST = '2K5-GH65'

const REGLES = {
    mastercard: {
        tailles: [4, 4, 4, 4],
        invite: 'Numéro de carte',
        message: 'Entrez les 16 chiffres de votre carte.',
        valide: (chiffres) => chiffres.length === 16,
    },
    mtn: {
        tailles: [3, 2, 2, 2],
        invite: 'Numéro MTN',
        message: 'Entrez un numéro MTN valide (9 chiffres).',
        valide: (chiffres) => /^6(5[0-4]|[78]\d)\d{6}$/.test(chiffres),
    },
    orange: {
        tailles: [3, 2, 2, 2],
        invite: 'Numéro Orange',
        message: 'Entrez un numéro Orange valide (9 chiffres).',
        valide: (chiffres) => /^6(5[5-9]|9\d)\d{6}$/.test(chiffres),
    },
}

const champCode = document.getElementById('code-promo')
const statutCode = document.getElementById('code-statut')
const boutonPayer = document.getElementById('payer')
const messageErreur = document.getElementById('paiement-erreur')
const lignesPaiement = document.querySelectorAll('[data-paiement]')
const reduireMouvements = window.matchMedia('(prefers-reduced-motion: reduce)').matches
const citeBouton = document.getElementById('cite-bouton')
const citeNom = document.getElementById('cite-nom')
const quartierNom = document.getElementById('quartier-nom')

const factureArticles = document.getElementById('facture-articles')
const factureParrainage = document.getElementById('facture-parrainage')
const factureLivraison = document.getElementById('facture-livraison')
const factureEmballage = document.getElementById('facture-emballage')
const factureTotal = document.getElementById('facture-total')

let totalCourant = 0
let paiementChoisi = null
let quartierCourant = QUARTIER_DEFAUT
let citeCourante = CITE_DEFAUT

function prixLigne(ligne) {
    const prixComplement = ligne.complement ? ligne.complement.prix : 0
    return (ligne.prixPlat + prixComplement) * ligne.quantite
}

function afficherStatutCode(valide, vide) {
    statutCode.classList.toggle('bg-or', valide)
    statutCode.classList.toggle('bg-[#D9D9D9]', !valide)
    statutCode.textContent = valide
        ? 'Code Promo Confirmé'
        : vide
          ? 'Entrez votre code'
          : 'Code invalide'
}

function afficherFacture() {
    const panier = lirePanier()
    const articles = panier.reduce((somme, ligne) => somme + prixLigne(ligne), 0)

    const code = champCode.value.trim().toUpperCase()
    const codeValide = code === CODE_TEST
    afficherStatutCode(codeValide, code === '')

    const parrainage = codeValide ? Math.round(articles * TAUX_PARRAINAGE) : 0
    const livraison = articles > 0 ? LIVRAISON : 0
    const emballage = articles > 0 ? EMBALLAGE : 0
    totalCourant = articles - parrainage + livraison + emballage

    factureArticles.textContent = articles
    factureParrainage.textContent = parrainage
    factureLivraison.textContent = livraison
    factureEmballage.textContent = emballage
    factureTotal.textContent = totalCourant

    boutonPayer.disabled = panier.length === 0
}

function afficherErreur(texte) {
    messageErreur.textContent = texte
    messageErreur.classList.remove('hidden')
}

function masquerErreur() {
    messageErreur.classList.add('hidden')
}

function formater(chiffres, tailles) {
    const morceaux = []
    let debut = 0
    for (const taille of tailles) {
        if (debut >= chiffres.length) break
        morceaux.push(chiffres.slice(debut, debut + taille))
        debut += taille
    }
    return morceaux.join(' ')
}

function chiffresDe(champ) {
    return champ.value.replace(/\D/g, '')
}

function selectionnerPaiement(id) {
    paiementChoisi = id
    masquerErreur()

    lignesPaiement.forEach((ligne) => {
        const actif = ligne.dataset.paiement === id
        const champ = ligne.querySelector('[data-role="numero"]')
        const logo = ligne.firstElementChild

        logo.classList.toggle('ring-2', actif)
        logo.classList.toggle('ring-or', actif)

        champ.readOnly = !actif
        champ.tabIndex = actif ? 0 : -1
        champ.classList.toggle('text-black', actif)
        champ.classList.toggle('text-[#8E8E93]', !actif)
        champ.classList.toggle('border-b', actif)
        champ.classList.toggle('border-[#8E8E93]', actif)
        champ.placeholder = actif ? REGLES[id].invite : ''

        if (!actif) champ.value = ''
        if (actif) champ.focus()
    })
}

lignesPaiement.forEach((ligne) => {
    const id = ligne.dataset.paiement
    const champ = ligne.querySelector('[data-role="numero"]')
    const regle = REGLES[id]

    ligne.addEventListener('click', () => {
        if (paiementChoisi !== id) selectionnerPaiement(id)
    })

    champ.addEventListener('input', () => {
        const chiffres = chiffresDe(champ).slice(0, regle.tailles.reduce((a, b) => a + b, 0))
        champ.value = formater(chiffres, regle.tailles)
        masquerErreur()
    })
})

const horsNom = document.getElementById('hors-nom')
const quartierChanger = document.getElementById('quartier-changer')

const modaleCites = document.getElementById('modale-cites')
const citesListe = document.getElementById('cites-liste')
const citesQuartier = document.getElementById('cites-quartier')
const citesQuartierHors = document.getElementById('cites-quartier-hors')
const citesFermer = document.getElementById('cites-fermer')
const citesChanger = document.getElementById('cites-changer')

const modaleCarte = document.getElementById('modale-carte')
const carteConteneur = document.getElementById('carte')
const carteSelection = document.getElementById('carte-selection')
const carteConfirmer = document.getElementById('carte-confirmer')
const carteAnnuler = document.getElementById('carte-annuler')
const carteFermer = document.getElementById('carte-fermer')

let carte = null
let marqueurs = {}
let quartierPropose = null

function ouvrir(modale) {
    modale.classList.remove('hidden')
    modale.classList.add('flex')
}

function fermer(modale) {
    modale.classList.add('hidden')
    modale.classList.remove('flex')
}

function afficherAdresse() {
    citeNom.textContent = citeCourante
    quartierNom.textContent = QUARTIERS[quartierCourant].nom
    horsNom.textContent = QUARTIERS[quartierCourant].nom
}

function remplirCites() {
    const quartier = QUARTIERS[quartierCourant]
    citesQuartier.textContent = quartier.nom
    citesQuartierHors.textContent = quartier.nom
    citesListe.replaceChildren()

    quartier.cites.forEach((cite) => {
        const element = document.createElement('li')
        const bouton = document.createElement('button')
        const actif = cite === citeCourante

        bouton.type = 'button'
        bouton.textContent = cite
        bouton.setAttribute('aria-current', String(actif))
        bouton.className = actif
            ? 'w-full rounded-[12px] bg-or px-4 py-3 text-left text-base font-semibold text-white'
            : 'w-full rounded-[12px] bg-[#D9D9D9] px-4 py-3 text-left text-base font-medium transition hover:brightness-95'

        bouton.addEventListener('click', () => {
            citeCourante = cite
            afficherAdresse()
            fermer(modaleCites)
            citeBouton.focus()
        })

        element.append(bouton)
        citesListe.append(element)
    })
}

function ouvrirCites() {
    remplirCites()
    ouvrir(modaleCites)
    citesListe.querySelector('button').focus()
}

function styleMarqueur(id, actif) {
    marqueurs[id].setStyle({
        radius: actif ? 12 : 8,
        color: '#ffffff',
        weight: 2,
        fillColor: actif ? '#CFBD97' : '#1E1E1E',
        fillOpacity: 1,
    })
}

function proposerQuartier(id) {
    if (quartierPropose) styleMarqueur(quartierPropose, false)
    quartierPropose = id
    styleMarqueur(id, true)
    marqueurs[id].bringToFront()
    carteSelection.textContent = `Quartier sélectionné : ${QUARTIERS[id].nom}`
    carteConfirmer.disabled = false
}

function quartierLePlusProche(point) {
    let meilleur = null
    let distanceMin = Infinity
    Object.entries(QUARTIERS).forEach(([id, quartier]) => {
        const distance = carte.distance(point, quartier.position)
        if (distance < distanceMin) {
            distanceMin = distance
            meilleur = id
        }
    })
    return meilleur
}

function creerCarte() {
    carte = L.map(carteConteneur, {
        center: CENTRE_DOUALA,
        zoom: 12,
        minZoom: 11,
        maxZoom: 18,
        maxBounds: BORNES_DOUALA,
        maxBoundsViscosity: 1,
    })

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; contributeurs OpenStreetMap',
        maxZoom: 19,
    }).addTo(carte)

    Object.entries(QUARTIERS).forEach(([id, quartier]) => {
        const marqueur = L.circleMarker(quartier.position, {
            radius: 8,
            color: '#ffffff',
            weight: 2,
            fillColor: '#1E1E1E',
            fillOpacity: 1,
            bubblingMouseEvents: false,
        }).addTo(carte)

        marqueur.bindTooltip(quartier.nom, { permanent: true, direction: 'top', offset: [0, -8] })
        marqueur.on('click', () => proposerQuartier(id))
        marqueurs[id] = marqueur
    })

    carte.on('click', (e) => proposerQuartier(quartierLePlusProche(e.latlng)))
}

function ouvrirCarte() {
    fermer(modaleCites)
    ouvrir(modaleCarte)
    if (!carte) creerCarte()
    carte.invalidateSize()
    carte.setView(QUARTIERS[quartierCourant].position, 13)
    quartierPropose = null
    Object.keys(marqueurs).forEach((id) => styleMarqueur(id, false))
    proposerQuartier(quartierCourant)
}

citeBouton.addEventListener('click', ouvrirCites)
citesFermer.addEventListener('click', () => fermer(modaleCites))
citesChanger.addEventListener('click', ouvrirCarte)
quartierChanger.addEventListener('click', ouvrirCarte)

carteFermer.addEventListener('click', () => fermer(modaleCarte))
carteAnnuler.addEventListener('click', () => fermer(modaleCarte))

carteConfirmer.addEventListener('click', () => {
    if (!quartierPropose) return
    if (quartierPropose !== quartierCourant) {
        quartierCourant = quartierPropose
        citeCourante = QUARTIERS[quartierCourant].cites[0]
    }
    afficherAdresse()
    fermer(modaleCarte)
    ouvrirCites()
})

modaleCites.addEventListener('click', (e) => {
    if (e.target === modaleCites) fermer(modaleCites)
})

modaleCarte.addEventListener('click', (e) => {
    if (e.target === modaleCarte) fermer(modaleCarte)
})

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return
    fermer(modaleCites)
    fermer(modaleCarte)
})

champCode.addEventListener('input', afficherFacture)

boutonPayer.addEventListener('click', () => {
    if (boutonPayer.disabled) return

    if (!paiementChoisi) {
        afficherErreur('Choisissez un moyen de paiement.')
        return
    }

    const champ = document.querySelector(`[data-paiement="${paiementChoisi}"] [data-role="numero"]`)
    const chiffres = chiffresDe(champ)

    if (!REGLES[paiementChoisi].valide(chiffres)) {
        afficherErreur(REGLES[paiementChoisi].message)
        champ.focus()
        return
    }

    const articles = lirePanier()

    const commande = {
        moyen: paiementChoisi,
        finNumero: chiffres.slice(-4),
        total: totalCourant,
        articles,
        adresse: {
            quartier: QUARTIERS[quartierCourant].nom,
            cite: citeCourante,
        },
    }

    enregistrerCommande({ lignes: articles, total: totalCourant })
    localStorage.setItem('commande-miam-miam', JSON.stringify(commande))
    viderPanier()

    window.location.href = '/pages/confirmation.html'
})


if (lirePanier().length === 0) {
    window.location.replace('/pages/panier.html')
}

afficherFacture()
afficherAdresse()

if (!reduireMouvements) {
    document
        .querySelectorAll('#colonne-gauche > section, #colonne-droite > div > *')
        .forEach((bloc, index) => {
            bloc.classList.add('bloc-entree')
            bloc.style.setProperty('--i', index)
        })
}