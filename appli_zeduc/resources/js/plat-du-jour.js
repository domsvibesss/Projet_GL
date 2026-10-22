import { ajouterLigne } from './panier.js'

const plats = [
    {
        nom: 'PORC ROTI',
        description: 'Description provisoire du porc roti.',
        prix: 2000,
        image: new URL('../assets/images/PorcRoti.jpeg', import.meta.url).href,
    },
    {
        nom: 'BOULETTES',
        description: 'Description provisoire .',
        prix: 1000,
        image: new URL('../assets/images/Boulettes.jpeg', import.meta.url).href,
    },
    {
        nom: 'ERU',
        description: 'Description provisoire .',
        prix: 1000,
        image: new URL('../assets/images/Eru.jpeg', import.meta.url).href,
    },
    {
        nom: 'NDOLE',
        description: 'Description provisoire .',
        prix: 1000,
        image: new URL('../assets/images/Ndole.jpeg', import.meta.url).href,
    },
    {
        nom: 'OKOK',
        description: 'Description provisoire .',
        prix: 1500,
        image: new URL('../assets/images/Okok.jpeg', import.meta.url).href,
    },
    {
        nom: 'POISSON BRAISE',
        description: 'Description provisoire .',
        prix: 2000,
        image: new URL('../assets/images/PoissonBraise.jpeg', import.meta.url).href,
    },
    {
        nom: 'POULET BRAISE',
        description: 'Description provisoire .',
        prix: 2000,
        image: new URL('../assets/images/PouletBraise.jpeg', import.meta.url).href,
    },
    {
        nom: 'POULET DG',
        description: 'Description provisoire .',
        prix: 2000,
        image: new URL('../assets/images/PouletDG.jpeg', import.meta.url).href,
    },
    {
        nom: 'POULET MAYO',
        description: 'Description provisoire .',
        prix: 2500,
        image: new URL('../assets/images/PouletMayo.jpeg', import.meta.url).href,
    },
    {
        nom: 'POULET PANE',
        description: 'Description provisoire .',
        prix: 2000,
        image: new URL('../assets/images/PouletPane.jpeg', import.meta.url).href,
    },
    {
        nom: 'POULET ROTI',
        description: 'Description provisoire .',
        prix: 1500,
        image: new URL('../assets/images/PouletRoti.jpeg', import.meta.url).href,
    },
    {
        nom: 'SPAGHETTI BOLOGNAISE',
        description: 'Description provisoire .',
        prix: 1000,
        image: new URL('../assets/images/SpaghettiBolognaise.jpeg', import.meta.url).href,
    },
]

const nomPlat = document.getElementById('plat-nom')
const descriptionPlat = document.getElementById('plat-description')
const prixPlat = document.getElementById('plat-prix')
const imagePlat = document.getElementById('plat-image')
const precedent = document.getElementById('plat-precedent')
const suivant = document.getElementById('plat-suivant')
const plus = document.getElementById('plus')
const moins = document.getElementById('moins')
const affichage = document.getElementById('quantite')

if (nomPlat && descriptionPlat && prixPlat && imagePlat && precedent && suivant && plus && moins && affichage) {
    const demande = new URLSearchParams(window.location.search).get('plat')
    const normaliser = (texte) => texte.normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toUpperCase()
    const trouve = demande ? plats.findIndex((plat) => normaliser(plat.nom) === normaliser(demande)) : -1
    let indexPlat = trouve >= 0 ? trouve : 0
    let quantite = 1
    const elementsAnimes = [nomPlat, descriptionPlat, prixPlat, imagePlat]
    let changementEnCours = false

    function afficherQuantite(){
        affichage.textContent = String(quantite).padStart(2, '0')
    }

    function afficherPlat(){
        const plat = plats[indexPlat]
        nomPlat.textContent = plat.nom
        descriptionPlat.textContent = plat.description
        prixPlat.textContent = `Prix: XAF${plat.prix}`
        imagePlat.src = plat.image
        imagePlat.alt = plat.nom
        quantite = 1
        afficherQuantite()
    }

    function changerPlat(nouvelIndex) {
        if (changementEnCours) return
        changementEnCours = true
        annulerTicket()

        const prochaine = new Image()
        prochaine.src = plats[nouvelIndex].image

        elementsAnimes.forEach((el) => el.classList.add('plat-cache'))

        Promise.all([
            new Promise((resolve) => setTimeout(resolve, 800)),
            prochaine.decode().catch(() => {}),
        ]).then(() => {
            indexPlat = nouvelIndex
            afficherPlat()
            elementsAnimes.forEach((el) => el.classList.remove('plat-cache'))
            setTimeout(() => {
                changementEnCours = false
            }, 800)
        })
    }
    suivant.addEventListener('click', () => {
        changerPlat((indexPlat + 1) % plats.length)
    })
    precedent.addEventListener('click', () => {
        changerPlat((indexPlat - 1 + plats.length) % plats.length)
    })
    plus.addEventListener('click', () => {
        if (quantite < 99) {
            quantite++
            afficherQuantite()
        }
    })
    moins.addEventListener('click', () => {
        if (quantite > 1) {
            quantite--
            afficherQuantite()
        }
    })
    afficherPlat()
}

const complements = document.querySelectorAll('[data-complement]')
let complementChoisi = null

function estSurLePlat(x, y) {
    const r = imagePlat.getBoundingClientRect()
    const centreX = r.left + r.width / 2
    const centreY = r.top + r.height / 2
    return Math.hypot(x - centreX, y - centreY) <= r.width / 2
}

function deposer(carte, dx, dy) {
    const r = carte.getBoundingClientRect()
    const p = imagePlat.getBoundingClientRect()

    const origineX = r.left + r.width / 2 - dx
    const origineY = r.top + r.height / 2 - dy
    const cibleX = p.left + p.width / 2 - origineX
    const cibleY = p.top + p.height / 2 - origineY

    carte.classList.add('complement-entre')
    carte.style.translate = `${cibleX}px ${cibleY}px`

    complementChoisi = {
        id: carte.dataset.complement,
        nom: carte.dataset.nom,
        prix: Number(carte.dataset.prix),
    }
    setTimeout(ouvrirTicket, 350)

    imagePlat.classList.add('plat-recoit')
    setTimeout(() => imagePlat.classList.remove('plat-recoit'), 400)

    setTimeout(() => {
        carte.classList.add('complement-invisible')
        carte.classList.remove('complement-entre')
        carte.style.translate = ''
        void carte.offsetWidth
        carte.classList.remove('complement-invisible')
    }, 500)
}

complements.forEach((carte) => {
    let departX = 0
    let departY = 0
    let dx = 0
    let dy = 0

    carte.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return
        if (carte.classList.contains('complement-entre')) return
        departX = e.clientX
        departY = e.clientY
        dx = 0
        dy = 0
        carte.setPointerCapture(e.pointerId)
        carte.classList.add('complement-saisi')
    })

    carte.addEventListener('pointermove', (e) => {
        if (!carte.classList.contains('complement-saisi')) return
        dx = e.clientX - departX
        dy = e.clientY - departY
        carte.style.translate = `${dx}px ${dy}px`
        imagePlat.classList.toggle('plat-survole', estSurLePlat(e.clientX, e.clientY))
    })

    function relacher(e) {
        if (!carte.classList.contains('complement-saisi')) return
        carte.classList.remove('complement-saisi')
        imagePlat.classList.remove('plat-survole')

        if (e.type === 'pointerup' && estSurLePlat(e.clientX, e.clientY)) {
            deposer(carte, dx, dy)
        } else {
            carte.style.translate = ''
        }
    }

    carte.addEventListener('pointerup', relacher)
    carte.addEventListener('pointercancel', relacher)
})

const ticket = document.getElementById('ticket')
const ticketPlat = document.getElementById('ticket-plat')
const ticketPlatPrix = document.getElementById('ticket-plat-prix')
const ticketComplement = document.getElementById('ticket-complement')
const ticketComplementPrix = document.getElementById('ticket-complement-prix')
const ticketTotal = document.getElementById('ticket-total')
const ticketConfirmer = document.getElementById('ticket-confirmer')
const ticketAnnuler = document.getElementById('ticket-annuler')

function ouvrirTicket() {
    if (!complementChoisi) return

    const qte = Number(affichage.textContent)
    const prixUnitePlat = Number(prixPlat.textContent.replace(/\D/g, ''))
    const totalPlat = prixUnitePlat * qte
    const totalComplement = complementChoisi.prix * qte

    ticketPlat.textContent = `${nomPlat.textContent.trim()} × ${qte}`
    ticketPlatPrix.textContent = `XAF${totalPlat}`
    ticketComplement.textContent = `+ ${complementChoisi.nom} × ${qte}`
    ticketComplementPrix.textContent = `XAF${totalComplement}`
    ticketTotal.textContent = `XAF${totalPlat + totalComplement}`

    const plat = imagePlat.getBoundingClientRect()
    const parent = ticket.offsetParent.getBoundingClientRect()
    ticket.style.top = `${plat.top - parent.top + plat.height / 2}px`

    ticket.classList.add('ticket-ouvert')
    ticketConfirmer.focus()
}

function fermerTicket() {
    ticket.classList.remove('ticket-ouvert')
}

function annulerTicket() {
    fermerTicket()
    complementChoisi = null
}

ticketAnnuler.addEventListener('click', annulerTicket)

const boutonAjouter = document.getElementById('ajouter')
const texteBoutonAjouter = boutonAjouter.textContent.trim()
let envolEnCours = false

function enregistrerDansLePanier() {
    ajouterLigne({
        plat: nomPlat.textContent.trim(),
        image: imagePlat.src,
        prixPlat: Number(prixPlat.textContent.replace(/\D/g, '')),
        complement: complementChoisi
            ? { nom: complementChoisi.nom, prix: complementChoisi.prix }
            : null,
        quantite: Number(affichage.textContent),
    })
}

function confirmerSurLeBouton() {
    boutonAjouter.classList.add('bouton-recoit')
    boutonAjouter.textContent = 'Ajouté ✓'

    setTimeout(() => {
        boutonAjouter.classList.remove('bouton-recoit')
        boutonAjouter.textContent = texteBoutonAjouter
    }, 1800)
}

ticketConfirmer.addEventListener('click', () => {
    if (envolEnCours) return
    envolEnCours = true

    enregistrerDansLePanier()

    const t = ticket.getBoundingClientRect()
    const b = boutonAjouter.getBoundingClientRect()
    const dx = b.left + b.width / 2 - (t.left + t.width / 2)
    const dy = b.top + b.height / 2 - (t.top + t.height / 2)

    ticket.style.translate = `${dx}px calc(-50% + ${dy}px)`
    ticket.classList.add('ticket-envol')

    setTimeout(confirmerSurLeBouton, 850)

    setTimeout(() => {
        ticket.style.transition = 'none'
        ticket.classList.remove('ticket-ouvert', 'ticket-envol')
        ticket.style.translate = ''
        void ticket.offsetWidth
        ticket.style.transition = ''
        complementChoisi = null
        envolEnCours = false
    }, 1000)
})

boutonAjouter.addEventListener('click', () => {
    if (envolEnCours || ticket.classList.contains('ticket-ouvert') || imagePlat.classList.contains('plat-cache')) return

    if (complementChoisi) {
        ouvrirTicket()
        return
    }

    enregistrerDansLePanier()
    confirmerSurLeBouton()
})

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') annulerTicket()
})