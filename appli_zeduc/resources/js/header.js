const nomPage = document.body.dataset.page || ''
const LIENS = [
    { id: 'menu', libelle: 'Menu', href: '/pages/menu.html', couleur: '207 189 151' },
    { id: 'panier', libelle: 'Panier', href: '/pages/panier.html', couleur: '90 200 130' },
    { id: 'historique', libelle: 'Historique', href: '/pages/historique.html', couleur: '176 141 87' },
    { id: 'fidelite', libelle: 'Fidélité', href: '/pages/fidelite.html', couleur: '143 184 201' },
    { id: 'reclamations', libelle: 'Réclamations', href: '/pages/reclamations.html', couleur: '225 100 100' },
]

const PAGES = {
    index: 'accueil',
    '': 'accueil',
    menu: 'menu',
    'plat-du-jour': 'menu',
    panier: 'panier',
    finalisation: 'panier',
    historique: 'historique',
    fidelite: 'fidelite',
    reclamations: 'reclamations',
}

const CLE = 'entete-precedent'
const COULEUR_NEUTRE = '207 189 151'
const RESSORT = 'cubic-bezier(0.34, 1.56, 0.64, 1)'
const TRANSITION = `left 0.7s ${RESSORT}, width 0.7s ${RESSORT}, opacity 0.4s ease`

const reduire = window.matchMedia('(prefers-reduced-motion: reduce)').matches
const courant = PAGES[nomPage]

function lirePrecedent() {
    try {
        return sessionStorage.getItem(CLE)
    } catch {
        return null
    }
}

function memoriser(id) {
    try {
        sessionStorage.setItem(CLE, id)
    } catch {}
}

function creerEntete() {
    const entete = document.createElement('header')
    entete.id = 'entete'
    entete.className = 'relative z-40 mx-4 mt-4 lg:mx-[2.6vw]'

    const liens = LIENS.map(
        (lien) => `
            <a data-id="${lien.id}" href="${lien.href}" ${lien.id === courant ? 'aria-current="page"' : ''} class="relative z-10 whitespace-nowrap rounded-full px-3 py-2 transition-colors hover:text-white lg:px-5 ${lien.id === courant ? 'text-white' : 'text-white/70'}">${lien.libelle}</a>`
    ).join('')

    entete.innerHTML = `
        <div data-role="halo" class="pointer-events-none absolute inset-x-[8%] -bottom-5 -z-10 h-14 rounded-full blur-2xl transition-colors duration-[800ms] motion-reduce:transition-none"></div>
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 rounded-[28px] border border-white/20 bg-white/10 px-4 py-2 shadow-lg backdrop-blur-xl lg:flex-nowrap lg:rounded-full lg:px-6">
            <a href="/index.html" class="order-1 font-handlee text-2xl text-or [text-shadow:2px_2px_2px_rgb(0_0_0/0.2)]">Mon MIAM MIAM</a>
            <nav data-role="nav" aria-label="Navigation principale" class="relative order-3 flex w-full items-center justify-between gap-1 overflow-x-auto font-gilda text-base lg:order-2 lg:w-auto lg:justify-center lg:gap-2 lg:overflow-visible lg:text-xl">
                <span data-role="indicateur" aria-hidden="true" class="absolute bottom-1 left-0 top-1 w-0 rounded-full border border-white/25 bg-white/15 shadow-[inset_0_1px_0_rgb(255_255_255/0.3)]"></span>
                ${liens}
            </nav>
            <a href="/pages/connexion.html" class="order-2 rounded-full border border-white/30 bg-or/40 px-5 py-2 font-gilda text-base text-white shadow-[inset_0_1px_0_rgb(255_255_255/0.4)] transition hover:bg-or/60 active:scale-95 lg:order-3 lg:text-xl">Connexion</a>
        </div>
    `
    return entete
}

if (courant) {
    const entete = creerEntete()
    document.body.prepend(entete)

    const nav = entete.querySelector('[data-role="nav"]')
    const indicateur = entete.querySelector('[data-role="indicateur"]')
    const halo = entete.querySelector('[data-role="halo"]')

    const placer = (id, animer) => {
        const lien = nav.querySelector(`[data-id="${id}"]`)
        indicateur.style.transition = animer && !reduire ? TRANSITION : 'none'
        if (!lien) {
            indicateur.style.opacity = '0'
            return
        }
        indicateur.style.opacity = '1'
        indicateur.style.left = `${lien.offsetLeft}px`
        indicateur.style.width = `${lien.offsetWidth}px`
    }

    const colorer = (id) => {
        const lien = LIENS.find((l) => l.id === id)
        halo.style.backgroundColor = `rgb(${lien ? lien.couleur : COULEUR_NEUTRE} / 0.45)`
    }

    const precedent = lirePrecedent()
    const depart = precedent && precedent !== courant ? precedent : courant

    placer(depart, false)
    colorer(depart)
    void entete.offsetWidth
    placer(courant, true)
    colorer(courant)
    memoriser(courant)

    window.addEventListener('resize', () => placer(courant, false))
}