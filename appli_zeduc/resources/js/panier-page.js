import { lirePanier, changerQuantite, retirerLigne } from './panier.js'

const LIVRAISON = 200
const EMBALLAGE = 100
const TAUX_PARRAINAGE = 0.01
const CODE_TEST = '2K5-GH65'

const liste = document.getElementById('liste-articles')
const modele = document.getElementById('modele-article')
const messageVide = document.getElementById('panier-vide')
const champCode = document.getElementById('code-promo')
const statutCode = document.getElementById('code-statut')
const boutonPayer = document.getElementById('payer')

const factureArticles = document.getElementById('facture-articles')
const factureParrainage = document.getElementById('facture-parrainage')
const factureLivraison = document.getElementById('facture-livraison')
const factureEmballage = document.getElementById('facture-emballage')
const factureTotal = document.getElementById('facture-total')

function prixLigne(ligne) {
    const prixComplement = ligne.complement ? ligne.complement.prix : 0
    return (ligne.prixPlat + prixComplement) * ligne.quantite
}

function afficherArticles() {
    const panier = lirePanier()
    liste.replaceChildren()
    messageVide.classList.toggle('hidden', panier.length > 0)

    panier.forEach((ligne, index) => {
        const carte = modele.content.firstElementChild.cloneNode(true)
        const el = (role) => carte.querySelector(`[data-role="${role}"]`)

        el('image').src = ligne.image
        el('image').alt = ligne.plat
        el('nom').textContent = ligne.plat
        el('complement').textContent = ligne.complement ? ligne.complement.nom : 'Sans complément'
        el('prix').textContent = prixLigne(ligne)
        el('quantite').textContent = ligne.quantite

        el('plus').addEventListener('click', () => {
            changerQuantite(index, 1)
            actualiser()
        })
        el('moins').addEventListener('click', () => {
            changerQuantite(index, -1)
            actualiser()
        })
        el('supprimer').addEventListener('click', () => {
            retirerLigne(index)
            actualiser()
        })

        liste.append(carte)
    })
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
    const total = articles - parrainage + livraison + emballage

    factureArticles.textContent = articles
    factureParrainage.textContent = parrainage
    factureLivraison.textContent = livraison
    factureEmballage.textContent = emballage
    factureTotal.textContent = total

    boutonPayer.disabled = panier.length === 0
}

function actualiser() {
    afficherArticles()
    afficherFacture()
}

champCode.addEventListener('input',() => afficherFacture())

actualiser(true)

boutonPayer.addEventListener('click', () => {
    if (boutonPayer.disabled) return
    window.location.href = '/pages/finalisation.html'
})