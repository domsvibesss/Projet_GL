import { lireCommandes } from './commandes.js'

const CLE = 'fidelite-miam-miam'

export const POINTS_COMMANDE = 15
export const POINTS_PARTAGE = 30

export const BADGES = [
    {
        id: 'bronze',
        nom: 'Bronze',
        seuil: 0,
        couleur: '#B08D57',
        avantages: ['Accès aux promotions de la semaine', 'Cumul de points sur chaque commande'],
    },
    {
        id: 'argent',
        nom: 'Argent',
        seuil: 500,
        couleur: '#A8A9AD',
        avantages: ['Tous les avantages Bronze', 'Un complément offert chaque mois'],
    },
    {
        id: 'or',
        nom: 'Or',
        seuil: 1500,
        couleur: '#CFBD97',
        avantages: ['Tous les avantages Argent', 'Livraison à prix réduit', 'Accès anticipé aux nouveaux plats'],
    },
    {
        id: 'platinum',
        nom: 'Platinum',
        seuil: 2500,
        couleur: '#8FB8C9',
        avantages: ['Tous les avantages Or', 'Livraison offerte', 'Un plat du jour offert chaque mois'],
    },
]

function aujourdhui() {
    const d = new Date()
    const p = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`
}

function lireBonus() {
    try {
        const donnees = JSON.parse(localStorage.getItem(CLE))
        return Array.isArray(donnees) ? donnees : []
    } catch {
        return []
    }
}

function sauvegarderBonus(bonus) {
    localStorage.setItem(CLE, JSON.stringify(bonus))
}

export function partageDisponible() {
    const jour = aujourdhui()
    return !lireBonus().some((b) => b.type === 'partage' && b.jour === jour)
}

export function ajouterBonusPartage() {
    if (!partageDisponible()) return false
    const bonus = lireBonus()
    bonus.push({
        type: 'partage',
        jour: aujourdhui(),
        date: new Date().toISOString(),
        points: POINTS_PARTAGE,
    })
    sauvegarderBonus(bonus)
    return true
}

export function calculerFidelite() {
    const gains = [
        ...lireCommandes().map((c) => ({
            libelle: c.plat,
            date: c.date,
            points: POINTS_COMMANDE,
        })),
        ...lireBonus().map((b) => ({
            libelle: 'Partage à un ami',
            date: b.date,
            points: b.points,
        })),
    ].sort((a, b) => new Date(b.date) - new Date(a.date))

    const total = gains.reduce((somme, g) => somme + g.points, 0)

    let index = 0
    BADGES.forEach((badge, i) => {
        if (total >= badge.seuil) index = i
    })

    return {
        total,
        gains,
        badge: BADGES[index],
        suivant: BADGES[index + 1] || null,
    }
}