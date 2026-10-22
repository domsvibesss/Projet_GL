# Documentation front-end — Espace Employé

**Projet :** Mon Miam Miam (application Laravel de gestion de restaurant)
**Partie :** interface de l'employé (assistant cuisinier)
**Auteur :** MESSANG LOUIS-MARIE EMMANUEL
**Technologies :** Laravel (Blade), Tailwind CSS (CDN), JavaScript natif
**Date :** octobre 2026

---

## 1. Présentation

L'espace Employé permet à un membre du personnel de cuisine de :

| Écran | Rôle |
|---|---|
| **Commandes** | Suivre les commandes du jour et les faire avancer (en attente → en préparation → prête → livrée) |
| **Plat du jour** | Choisir le plat du jour et planifier les plats servis chaque jour de la semaine |
| **Réclamations** | Lire les réclamations des clients et y répondre |
| **Statistiques** | Voir le nombre de commandes par jour sur la semaine |
| **Statistique journalière** | Voir le détail des plats commandés un jour donné |

L'interface reproduit la maquette Figma du projet (fond noir, cartes beige, police à empattements pour les titres).

> **État actuel :** c'est une interface **front-end**. Les données sont écrites en dur dans `routes/web.php` et les actions (changer un statut, répondre à une réclamation…) ne sont **pas enregistrées** : elles disparaissent au rechargement de la page. La partie 9 liste ce qu'il faut brancher sur le back-end.

---

## 2. Arborescence des fichiers

```
routes/
└── web.php                                  Routes + données de démonstration

resources/views/
├── layouts/
│   ├── app.blade.php                        Squelette HTML commun (head, Tailwind, polices)
│   └── employe.blade.php                    Layout employé = app + barre de menu employé
├── partials/
│   ├── nav.blade.php                        Barre de navigation (réutilisable)
│   └── utilisateur.blade.php                Bloc nom + avatar + menu Deconnexion
└── employe/
    ├── commandes.blade.php
    ├── plat-du-jour.blade.php
    ├── reclamations.blade.php
    ├── statistiques.blade.php
    └── statistique-journalier.blade.php

public/
├── js/
│   └── tailwind.config.js                   Couleurs et polices du projet
└── images/
    ├── logo.png                             Logo du restaurant
    ├── chef.png                             Avatar de l'employé connecté
    ├── fond-commandes.jpg                   Photo de fond de la page Commandes
    ├── fond-stats.jpg                       Photo de fond des pages Statistiques
    └── plats/                               Une photo par plat (voir partie 7)
```

---

## 3. Routes

Toutes les routes employé sont regroupées sous le préfixe `/employe` et le nom `employe.` :

```php
Route::prefix('employe')->name('employe.')->group(function () { ... });
```

| URL | Nom de la route | Vue | Données envoyées |
|---|---|---|---|
| `/` | — | redirige vers `/employe/commandes` | — |
| `/employe/commandes` | `employe.commandes` | `employe.commandes` | `$commandes`, `$compteurs` |
| `/employe/plat-du-jour` | `employe.plat-du-jour` | `employe.plat-du-jour` | `$platDuJour`, `$plats`, `$jours` |
| `/employe/reclamations` | `employe.reclamations` | `employe.reclamations` | `$reclamations` |
| `/employe/statistiques` | `employe.statistiques` | `employe.statistiques` | `$stats`, `$resume` |
| `/employe/statistiques/{jour}` | `employe.statistique-journalier` | `employe.statistique-journalier` | `$nomJour`, `$total`, `$plats` |

`{jour}` est un code court : `lun`, `mar`, `mer`, `jeu`, `ven`, `sam`, `dim`. Si le code est inconnu, la page affiche lundi.

Dans les vues, les liens utilisent toujours `route('nom.de.la.route')` et jamais une URL écrite à la main. Si une URL change dans `web.php`, tous les liens suivent automatiquement.

### Données partagées

Trois tableaux sont définis une seule fois en haut de `web.php` et réutilisés par plusieurs routes (ils servent aussi à l'espace Gérant) :

- `$commandes` : les 4 commandes affichées dans le tableau ;
- `$compteurs` : les 4 compteurs (en attente, en préparation, prêtes, livrées) ;
- `$statsSemaine` : le nombre de commandes par jour.

Ils sont transmis aux groupes de routes avec `use (...)`.

---

## 4. Layouts et composants communs

### 4.1 `layouts/app.blade.php` — squelette de toutes les pages

Contient le `<head>` commun :

- le jeton CSRF (`<meta name="csrf-token">`), utile plus tard pour les requêtes Axios ;
- les polices Google : **Inter** (tableaux, chiffres), **Lora** (menu, titres), **Lilita One** (logo) ;
- Tailwind via CDN, puis `public/js/tailwind.config.js` (doit être chargé **après** le CDN).

Les « trous » que chaque page remplit :

| Directive | Rôle |
|---|---|
| `@yield('titre')` | Titre de l'onglet (« Commandes – Mon miam miam ») |
| `@yield('police', 'font-sans')` | Police par défaut du `<body>` (une page peut la changer, ex. Plat du jour en `font-serif`) |
| `@yield('entete')` | Barre de navigation |
| `@yield('contenu')` | Contenu de la page |
| `@stack('styles')` / `@stack('scripts')` | CSS et JavaScript propres à une page, ajoutés avec `@push` |

### 4.2 `layouts/employe.blade.php` — layout de l'espace employé

Étend `layouts.app` et remplit la section `entete` avec la barre de menu employé :

- liens : Commandes, Plat du jour, Reclamations, Statistiques ;
- utilisateur affiché : **Valdes KENMOE**, Assistant cuisinier, avatar `images/chef.png`.

Une page employé commence donc simplement par :

```blade
@extends('layouts.employe')
@section('titre', 'Commandes')
@section('contenu')
    ...
@endsection
```

### 4.3 `partials/nav.blade.php` — barre de navigation

Composant réutilisable (employé **et** gérant). Il reçoit :

| Variable | Contenu |
|---|---|
| `$accueil` | Route du logo (page d'accueil de l'espace) |
| `$liens` | Tableau `nom de route => texte du lien` |
| `$utilisateur` | Tableau `nom`, `role`, `image`, `emoji` transmis à `partials.utilisateur` |

Le lien de la page en cours est colorié en or grâce à :

```blade
request()->routeIs($route) ? 'text-miam-or' : 'text-white hover:text-miam-or'
```

Le texte des liens est affiché avec `{!! !!}` (non échappé) pour autoriser un `<br>` dans les libellés sur deux lignes.

### 4.4 `partials/utilisateur.blade.php` — bloc utilisateur

Affiche le nom, le rôle et l'avatar en haut à droite. Au survol, un menu **Deconnexion** apparaît (classes Tailwind `group` / `group-hover:block`).

Si l'image de l'avatar est introuvable, `onerror="this.remove()"` la supprime et l'emoji placé derrière reste visible. Ce principe « emoji de secours » est utilisé pour toutes les images du projet.

> Le lien Deconnexion pointe vers `#` : il faudra le relier à la route de déconnexion du back-end.

---

## 5. Charte graphique (`public/js/tailwind.config.js`)

Les couleurs de la maquette sont déclarées une seule fois et utilisées sous forme de classes Tailwind (`bg-miam-beige`, `text-miam-or`…).

| Nom | Code | Utilisation |
|---|---|---|
| `miam-noir` | `#000000` | Fond des pages |
| `miam-beige` | `#D2BF96` | Cartes des jours, bandeau plat du jour, boutons |
| `miam-sable` | `#9C8A68` | Compteurs, cartes de statistiques |
| `miam-or` | `#C8A96A` | Lien actif du menu |
| `miam-creme` | `#E8E3DA` | En-têtes de tableaux, ligne sélectionnée |
| `miam-gris` | `#D9D9D9` | Blocs gris (réponse aux réclamations, graphique) |
| `miam-taupe` | `#5E5443` | Bouton Annuler |

| Police | Classe | Utilisation |
|---|---|---|
| Lilita One | `font-logo` | Logo « Mon miam miam » |
| Lora | `font-serif` | Menu, titres |
| Inter | `font-sans` | Tableaux, chiffres (police par défaut) |

Pour modifier une couleur dans tout le projet, il suffit de changer sa valeur dans ce fichier.

---

## 6. Fonctionnement page par page

Principe général : **Blade affiche la page, JavaScript la rend interactive.**

- Les parties fixes sont générées côté serveur avec `@foreach`.
- Les parties qui changent après un clic sont dessinées en JavaScript. Les données PHP sont transmises au script avec `@json($variable)`, qui les convertit en objet JavaScript.

### 6.1 Commandes — `employe/commandes.blade.php`

**Affichage**
- Photo de fond `images/fond-commandes.jpg` (ou `.png`) sous un voile noir à 40 % (`bg-black/40`).
- 4 compteurs générés par `@foreach ($compteurs)`. Chaque nombre porte un attribut `data-compteur="En attente"` (etc.) pour que le JavaScript le retrouve.
- Tableau des commandes généré par `@foreach ($commandes)`. Les cellules Statut et Action ont un identifiant (`statut-0`, `action-0`…) pour être mises à jour sans recharger la page.

**Interactions**

Chaque commande suit ce cycle, piloté par deux objets JavaScript :

| Statut actuel | Bouton Action | Statut suivant |
|---|---|---|
| En attente | Préparer | En préparation |
| En préparation | Valider | Prête |
| Prête | Livrer | Livrée |
| Livrée | — (pas de bouton) | — |

| Fonction | Rôle |
|---|---|
| `afficherAction(index)` | Écrit le statut et le bon bouton dans la ligne `index` |
| `avancerCommande(index)` | Passe la commande au statut suivant, retire 1 au compteur de l'ancien statut et ajoute 1 au nouveau |
| `afficherCompteurs()` | Réécrit les 4 nombres à partir de l'objet `compteurs` |

### 6.2 Plat du jour — `employe/plat-du-jour.blade.php`

**Affichage**
- Bandeau beige : photo et nom du plat du jour, bouton « changer le plat du jour ».
- Encadré « Menu de la semaine » (dates de la semaine, rappel du plat du jour).
- Grille de 7 colonnes (Lundi → Dimanche). Dimanche a un style inversé (fond noir, bordure beige) comme sur la maquette.
- Panneau latéral caché à droite, qui glisse à l'écran quand on l'ouvre.

**Le panneau latéral** sert dans deux cas :

| Ouvert depuis | Titre | Sélection | Au clic sur Valider |
|---|---|---|---|
| « + Ajouter un ou plusieurs plats » d'un jour | « Ajouter des plats pour jeudi » | plusieurs plats, les plats déjà prévus sont pré-cochés | la liste du jour est remplacée et la grille redessinée |
| « changer le plat du jour » | « Changer le plat du jour » | un seul plat (cocher un plat décoche les autres) | le nom, le rappel et la photo du plat du jour sont mis à jour |

Le panneau contient une recherche (filtre la liste à chaque frappe), un compteur « n plat(s) selectionne(s) » et les boutons Annuler / Valider. Il se ferme aussi avec la touche **Échap** ou en cliquant sur le fond sombre.

**État en mémoire (JavaScript)**

| Variable | Contenu |
|---|---|
| `PLATS` | Catalogue des plats (`nom`, `emoji`), venant de `$plats` |
| `JOURS` | Les 7 jours, venant de `$jours` |
| `menuSemaine` | `{ Lundi: [...], Mardi: [...], ... }`, vide au départ |
| `platDuJour` | Nom du plat du jour |
| `jourEnCours` | Jour pour lequel le panneau est ouvert, ou `"PLAT_DU_JOUR"` |
| `selection` | `Set` des plats cochés (un `Set` évite les doublons) |

**Fonctions principales**

| Fonction | Rôle |
|---|---|
| `afficherJours()` | Dessine les 7 colonnes à partir de `menuSemaine` |
| `ouvrirPanneau(jour)` / `fermerPanneau()` | Affiche ou cache le panneau (classe `translate-x-[110%]`) et le fond sombre |
| `afficherListePlats()` | Dessine la liste des plats filtrée par la recherche |
| `basculerPlat(nom, coche)` | Coche ou décoche un plat ; en mode plat du jour, garde un seul plat |
| `validerSelection()` | Applique la sélection puis ferme le panneau |
| `afficherPhotoPlatDuJour()` | Met à jour la photo du bandeau |
| `nomFichier(nom)` | Transforme un nom de plat en nom de fichier image (partie 7) |
| `balisePhoto(nom)` / `essayerPng(img)` | Crée la balise `<img>` du plat ; si le `.jpg` n'existe pas, essaie le `.png`, sinon laisse l'emoji |

### 6.3 Réclamations — `employe/reclamations.blade.php`

**Affichage**
- Tableau blanc : Id commande, Plat, Date et heure d'achat, Prix, pastille de statut.
- Bloc gris en dessous : la réclamation sélectionnée (à gauche) et la zone de réponse (à droite).

| Statut | Couleur de la pastille |
|---|---|
| En cours | beige / jaune |
| Traitée | vert |
| En attente | rose / rouge |

**Interactions**

| Fonction | Rôle |
|---|---|
| `afficherReclamations()` | Dessine le tableau ; la ligne sélectionnée est surlignée en crème |
| `selectionner(i)` | Affiche le numéro et le message de la réclamation `i`, vide la zone de réponse |
| `envoyerReponse()` | Si la réponse est vide, la bordure devient rouge 1,5 s ; sinon la réclamation passe en « Traitée » et « Réponse envoyée ✓ » s'affiche |

Au chargement, la dernière réclamation de la liste est sélectionnée.

### 6.4 Statistiques — `employe/statistiques.blade.php`

Page entièrement générée en Blade, **sans JavaScript**.

- 3 cartes résumé (`$resume`) : nombre total de commandes, plat le plus vendu, plat le moins vendu.
- Graphique en barres « Commandes par jour » fait en HTML/CSS (sans librairie) :
  - la hauteur de chaque barre vaut `valeur / max × 100 %`, calculée dans un bloc `@php` ;
  - les **2 meilleurs jours** sont en noir, les autres en beige (`$top2`, calculé avec `collect()->sortByDesc()->take(2)`) ;
  - chaque barre est un lien vers le détail du jour (`route('employe.statistique-journalier', $jour['code'])`).

### 6.5 Statistique journalière — `employe/statistique-journalier.blade.php`

Page générée en Blade, **sans JavaScript**.

- N'utilise pas le layout employé (pas de barre de menu, comme sur la maquette) mais directement `layouts.app`. Elle affiche un lien « ← Statistiques » et le bloc utilisateur (`@include('partials.utilisateur')`).
- Carte grise : nom du jour, total de commandes, liste des plats.
- Les nombres sont affichés sur deux chiffres avec `str_pad` (5 → « 05 »).
- `@forelse` / `@empty` affiche « Détail non disponible pour ce jour. » quand il n'y a pas de détail. Pour l'instant, seul **lundi** a un détail dans les données de démonstration.

---

## 7. Images

| Fichier | Où il apparaît |
|---|---|
| `public/images/logo.png` | Logo dans la barre de menu |
| `public/images/chef.png` | Avatar de l'employé |
| `public/images/fond-commandes.jpg` | Fond de la page Commandes |
| `public/images/fond-stats.jpg` | Fond des pages Statistiques |
| `public/images/plats/<nom>.jpg` | Photo de chaque plat (bandeau et panneau latéral) |

**Nommage des photos de plats.** La fonction `nomFichier()` déduit le nom du fichier à partir du nom du plat : minuscules, accents retirés, espaces et symboles remplacés par des tirets.

| Plat | Fichier attendu |
|---|---|
| Poulet DG | `plats/poulet-dg.jpg` |
| Porc braise | `plats/porc-braise.jpg` |
| Poisson braise | `plats/poisson-braise.jpg` |
| Water fufu + eru | `plats/water-fufu-eru.jpg` |
| Okok sucre + baton de manioc | `plats/okok-sucre-baton-de-manioc.jpg` |
| Ndole | `plats/ndole.jpg` |

Le `.png` est aussi accepté. Toutes les images passent par `asset()` pour fonctionner quelle que soit l'adresse du site. Si une image manque, un emoji ou un fond noir s'affiche à la place, sans casser la page.

**Ajouter un plat :** ajouter une ligne dans le tableau `plats` de la route `employe.plat-du-jour` dans `web.php`, puis déposer sa photo dans `public/images/plats/` avec le nom calculé ci-dessus.

---

## 8. Installation et lancement

1. Copier `routes/`, `resources/views/` (dossiers `layouts`, `partials`, `employe`) et `public/` (dossiers `js`, `images`) dans le projet Laravel.
2. Si `routes/web.php` contient déjà des routes, y ajouter le groupe `employe` plutôt que remplacer le fichier.
3. Lancer le serveur :
   ```bash
   php artisan serve
   ```
4. Ouvrir `http://localhost:8000/employe/commandes`.

Aucune installation npm n'est nécessaire : Tailwind est chargé par CDN (une connexion Internet est donc requise).

---

## 9. Raccordement au back-end (à faire)

| Où | Aujourd'hui | À faire |
|---|---|---|
| `web.php` (toutes les routes) | Données écrites en dur | Remplacer les tableaux par des requêtes en base (modèles Eloquent) dans des contrôleurs, ex. `EmployeController` |
| Commandes — `avancerCommande()` | Change le statut dans la page uniquement | Envoyer le nouveau statut au serveur (ex. `PUT /api/commandes/{id}`) |
| Plat du jour — `validerSelection()` | Plat du jour et menu de la semaine non enregistrés | Enregistrer le plat du jour et le menu (ex. `POST /api/menu`) |
| Réclamations — `envoyerReponse()` | Réponse non envoyée | Enregistrer la réponse et le statut (ex. `POST /api/reclamations/{id}/reponse`) |
| Statistiques | Chiffres fixes | Les calculer à partir des commandes en base |
| Statistique journalière | Détail seulement pour lundi | Calculer le détail de chaque jour |
| Bloc utilisateur | Valdes KENMOE écrit en dur dans `layouts/employe.blade.php` | Utiliser l'utilisateur connecté (`auth()->user()`) |
| Deconnexion | Lien `#` | Pointer vers la route de déconnexion |
| Dates de la semaine (Plat du jour) | « 12 au 18 octobre 2026 » écrit en dur | Les calculer avec Carbon |

Pour les requêtes POST/PUT, le jeton CSRF est déjà disponible dans `<meta name="csrf-token">`.

---

## 10. Limites connues

- Tailwind par **CDN** : pratique en développement, déconseillé en production. À terme, compiler Tailwind avec Vite (`npm install`, `npm run build`).
- Les actions ne sont pas enregistrées (voir partie 9) : un rechargement de la page remet tout à zéro.
- La mise en page est pensée pour un **écran d'ordinateur** ; elle n'est pas adaptée au mobile.
- Les messages des réclamations #ND0987 et #KK2341 sont des exemples : la maquette ne montre que celui de #KK6749.
