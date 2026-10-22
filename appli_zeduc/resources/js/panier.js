const CLE = 'panier-miam-miam'

export function lirePanier() {
    try {
        const donnees = JSON.parse(localStorage.getItem(CLE))
        return Array.isArray(donnees) ? donnees : []
    } catch {
        return []
    }
}

function sauvegarder(panier) {
    localStorage.setItem(CLE, JSON.stringify(panier))
}

export function changerQuantite(index, delta) {
    const panier = lirePanier()
    const ligne = panier[index]
    if (!ligne) return
    ligne.quantite = Math.min(99, Math.max(1, ligne.quantite + delta))
    sauvegarder(panier)
}

export function retirerLigne(index) {
    const panier = lirePanier()
    panier.splice(index, 1)
    sauvegarder(panier)
}

export function ajouterLigne(nouvelle) {
    const panier = lirePanier()
    const nomComplement = nouvelle.complement ? nouvelle.complement.nom : null
    const existante = panier.find(
        (ligne) =>
            ligne.plat === nouvelle.plat &&
            (ligne.complement ? ligne.complement.nom : null) === nomComplement
    )

    if (existante) {
        existante.quantite = Math.min(99, existante.quantite + nouvelle.quantite)
    } else {
        panier.push(nouvelle)
    }

    sauvegarder(panier)
}

export function viderPanier() {
    localStorage.removeItem(CLE)
}