const CLE = 'commandes'

export function lireCommandes() {
    try {
        const liste = JSON.parse(localStorage.getItem(CLE) || '[]')
        return Array.isArray(liste) ? liste : []
    } catch {
        return []
    }
}

function genererId(plat, existants) {
    const lettres = plat.replace(/[^A-Za-z]/g, '').slice(0, 2).toUpperCase().padEnd(2, 'X')
    let id
    do {
        id = lettres + String(Math.floor(Math.random() * 10000)).padStart(4, '0')
    } while (existants.some((c) => c.id === id))
    return id
}

export function enregistrerCommande({ lignes, total }) {
    const liste = lireCommandes()
    const plat = lignes
        .map((l) => {
            const quantite = l.quantite > 1 ? `${l.quantite}× ` : ''
            const complement = l.complement ? ` (${l.complement.nom})` : ''
            return `${quantite}${l.plat}${complement}`
        })
        .join(', ')

    const commande = {
        id: genererId(lignes[0].plat, liste),
        plat,
        date: new Date().toISOString(),
        prix: total,
    }

    liste.push(commande)
    localStorage.setItem(CLE, JSON.stringify(liste))
    return commande
}