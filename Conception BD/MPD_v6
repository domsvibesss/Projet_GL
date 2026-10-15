-- =====================================================================
-- MPD (Modèle Physique de Données) - version 6
-- Structure (tables, clés primaires et étrangères) : MLD_GL_v3 (22 tables)
-- Types, tailles, ENUM, CHECK, valeurs par défaut  : Dictionnaire de données v2
-- SGBD : MySQL 8.0.16 ou plus (les CHECK ne sont appliqués qu'à partir de cette version)
--
-- Convention :
--   * "contrainte à préciser" = attribut du MLD absent du dictionnaire
--     ou dont la contrainte n'y est pas définie.
--   * Contrainte transversale : tout attribut relatif aux points est >= 0
--     (points_fidelite, points_accumules, points_depenses, POINTS.quantite).
--   * Le MLD n'indique ni clés primaires ni colonnes nullables : les clés
--     primaires sont déduites (identifiant de l'entité, ou couple de clés
--     étrangères pour les tables de jointure).
-- =====================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS zeduc_space_database;
CREATE DATABASE zeduc_space_database
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE zeduc_space_database;

-- =====================================================================
-- 1. ENTITÉS SANS DÉPENDANCE
-- =====================================================================

CREATE TABLE ADMINISTRATEUR (
    id                   VARCHAR(8)    NOT NULL,                           -- format adm-nnnn
    poste                ENUM('PDG','Directeur marketing','Directeur financier','Responsable informatique') NOT NULL,
    nom                  VARCHAR(25)   NOT NULL,
    prenom               VARCHAR(25)   NOT NULL,
    tel                  VARCHAR(15)   NOT NULL,
    email                VARCHAR(50)   NOT NULL,
    mdp                  VARCHAR(255)  NOT NULL,                           -- mot de passe haché
    date_naissance       DATE          NOT NULL,
    date_enregistrement  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_administrateur PRIMARY KEY (id),
    CONSTRAINT uq_administrateur_email UNIQUE (email),
    CONSTRAINT ck_administrateur_id CHECK (id REGEXP '^adm-[0-9]{4}$')
) ENGINE=InnoDB;

CREATE TABLE GERANT (
    id                   VARCHAR(8)    NOT NULL,                           -- format grt-nnnn
    nom                  VARCHAR(25)   NOT NULL,
    prenom               VARCHAR(25)   NOT NULL,
    tel                  VARCHAR(15)   NOT NULL,
    email                VARCHAR(50)   NOT NULL,
    mdp                  VARCHAR(255)  NOT NULL,
    date_naissance       DATE          NOT NULL,
    date_enregistrement  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_gerant PRIMARY KEY (id),
    CONSTRAINT uq_gerant_email UNIQUE (email),
    CONSTRAINT ck_gerant_id CHECK (id REGEXP '^grt-[0-9]{4}$')
) ENGINE=InnoDB;

CREATE TABLE FAVORI (
    id_favori            INT       NOT NULL AUTO_INCREMENT,
    date_enregistrement  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_favori PRIMARY KEY (id_favori)
) ENGINE=InnoDB;

CREATE TABLE ETUDIANT (
    id                VARCHAR(8)    NOT NULL,                              -- format cli-nnnn
    nom               VARCHAR(25)   NOT NULL,
    prenom            VARCHAR(25)   NOT NULL,
    tel               VARCHAR(15)   NOT NULL,
    email             VARCHAR(50)   NOT NULL,
    mdp               VARCHAR(255)  NOT NULL,
    points_fidelite   INT           NOT NULL DEFAULT 0,
    code_parrain      VARCHAR(12)   NOT NULL,
    date_inscription  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut            ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    CONSTRAINT pk_etudiant PRIMARY KEY (id),
    CONSTRAINT uq_etudiant_email UNIQUE (email),
    CONSTRAINT uq_etudiant_code_parrain UNIQUE (code_parrain),
    CONSTRAINT ck_etudiant_id CHECK (id REGEXP '^cli-[0-9]{4}$'),
    CONSTRAINT ck_etudiant_points CHECK (points_fidelite >= 0)
) ENGINE=InnoDB;

-- =====================================================================
-- 2. EMPLOYE
-- =====================================================================

CREATE TABLE EMPLOYE (
    id                   VARCHAR(8)    NOT NULL,                           -- format zds-nnnn
    poste                ENUM('Cuisinier','Caissier','Livreur') NOT NULL,
    nom                  VARCHAR(25)   NOT NULL,
    prenom               VARCHAR(25)   NOT NULL,
    tel                  VARCHAR(15)   NOT NULL,
    email                VARCHAR(50)   NOT NULL,
    mdp                  VARCHAR(255)  NOT NULL,
    date_naissance       DATE          NOT NULL,
    date_enregistrement  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut               ENUM('actif','parti') NOT NULL DEFAULT 'actif',
    id_gerant            VARCHAR(8)    NOT NULL,
    CONSTRAINT pk_employe PRIMARY KEY (id),
    CONSTRAINT uq_employe_email UNIQUE (email),
    CONSTRAINT ck_employe_id CHECK (id REGEXP '^zds-[0-9]{4}$'),
    CONSTRAINT fk_employe_gerant FOREIGN KEY (id_gerant)
        REFERENCES GERANT (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 3. COMMANDE
-- =====================================================================

CREATE TABLE COMMANDE (
    id_commande       INT          NOT NULL AUTO_INCREMENT,
    montant_local     INT          NOT NULL,                               -- en FCFA
    points_accumules  INT          NOT NULL DEFAULT 0,
    date_commande     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    etat              ENUM('effectué','en_cours','rejeté','en_attente') NOT NULL DEFAULT 'en_attente',
    id_employe        VARCHAR(8)   NOT NULL,
    id_etudiant       VARCHAR(8)   NOT NULL,
    CONSTRAINT pk_commande PRIMARY KEY (id_commande),
    CONSTRAINT ck_commande_montant CHECK (montant_local >= 0),
    CONSTRAINT ck_commande_points CHECK (points_accumules >= 0),
    CONSTRAINT fk_commande_employe FOREIGN KEY (id_employe)
        REFERENCES EMPLOYE (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_commande_etudiant FOREIGN KEY (id_etudiant)
        REFERENCES ETUDIANT (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 4. PLAT (catalogue : quantite = stock global), PROMOTION, JEU
-- =====================================================================

CREATE TABLE PLAT (
    id                 VARCHAR(8)   NOT NULL,                              -- format plt-nnnn
    nom                VARCHAR(25)  NOT NULL,
    description        TEXT         NOT NULL,
    prix               INT          NOT NULL,                              -- en FCFA
    quantite           INT          NOT NULL,                              -- stock global (la quantité commandée est dans CONTENIR)
    id_administrateur  VARCHAR(8)   NOT NULL,
    CONSTRAINT pk_plat PRIMARY KEY (id),
    CONSTRAINT ck_plat_id CHECK (id REGEXP '^plt-[0-9]{4}$'),
    CONSTRAINT ck_plat_prix CHECK (prix >= 0),
    CONSTRAINT ck_plat_quantite CHECK (quantite >= 0),
    CONSTRAINT fk_plat_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PROMOTION (
    id                 INT          NOT NULL AUTO_INCREMENT,
    prix_initial       INT          NOT NULL,
    prix_promo         INT          NOT NULL,
    date_debut         DATETIME     NOT NULL,
    date_fin           DATETIME     NOT NULL,
    id_administrateur  VARCHAR(8)   NOT NULL,
    CONSTRAINT pk_promotion PRIMARY KEY (id),
    CONSTRAINT ck_promotion_prix_initial CHECK (prix_initial >= 0),
    CONSTRAINT ck_promotion_prix_promo CHECK (prix_promo < prix_initial),
    CONSTRAINT ck_promotion_dates CHECK (date_fin > date_debut),
    CONSTRAINT fk_promotion_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE JEU (
    id            VARCHAR(8)    NOT NULL,                                  -- format jeu-nnnn
    nom           VARCHAR(25)   NOT NULL,
    description   TEXT          NOT NULL,
    image         VARCHAR(255)  NULL,                                      -- chemin ou URL
    date_debut    DATETIME      NOT NULL,
    date_fin      DATETIME      NOT NULL,
    CONSTRAINT pk_jeu PRIMARY KEY (id),
    CONSTRAINT ck_jeu_id CHECK (id REGEXP '^jeu-[0-9]{4}$'),
    CONSTRAINT ck_jeu_dates CHECK (date_fin > date_debut)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. PAIEMENT, POINTS
-- =====================================================================

CREATE TABLE PAIEMENT (
    id_paiement      INT         NOT NULL AUTO_INCREMENT,
    montant_depense  INT         NULL,                                     -- NULL si payé en points
    points_depenses  INT         NOT NULL DEFAULT 0,
    date_paiement    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_employe       VARCHAR(8)  NOT NULL,
    id_commande      INT         NOT NULL,
    CONSTRAINT pk_paiement PRIMARY KEY (id_paiement),
    CONSTRAINT ck_paiement_montant CHECK (montant_depense IS NULL OR montant_depense >= 0),
    CONSTRAINT ck_paiement_points CHECK (points_depenses >= 0),
    CONSTRAINT fk_paiement_employe FOREIGN KEY (id_employe)
        REFERENCES EMPLOYE (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_paiement_commande FOREIGN KEY (id_commande)
        REFERENCES COMMANDE (id_commande) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE POINTS (
    id               INT         NOT NULL AUTO_INCREMENT,
    quantite         INT         NOT NULL,
    origine          ENUM('jeux','parrainage','achat') NOT NULL,
    date_reception   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_expiration  DATETIME    NOT NULL,
    id_commande      INT         NOT NULL,
    id_etudiant      VARCHAR(8)  NOT NULL,
    id_jeu           VARCHAR(8)  NULL,                                     -- nullabilité : contrainte à préciser
    CONSTRAINT pk_points PRIMARY KEY (id),
    CONSTRAINT ck_points_quantite CHECK (quantite >= 0),
    CONSTRAINT ck_points_dates CHECK (date_expiration > date_reception),
    CONSTRAINT fk_points_commande FOREIGN KEY (id_commande)
        REFERENCES COMMANDE (id_commande) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_points_etudiant FOREIGN KEY (id_etudiant)
        REFERENCES ETUDIANT (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_points_jeu FOREIGN KEY (id_jeu)
        REFERENCES JEU (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 6. RECLAMATION, REPONSE
-- =====================================================================

CREATE TABLE RECLAMATION (
    id_reclamation    INT         NOT NULL AUTO_INCREMENT,
    type_reclamation  ENUM('Plainte','Avis') NOT NULL DEFAULT 'Avis',
    description       TEXT        NOT NULL,
    statut            ENUM('accepté','rejeté','en_cours') NOT NULL DEFAULT 'en_cours',
    id_etudiant       VARCHAR(8)  NOT NULL,
    id_gerant         VARCHAR(8)  NULL,                                    -- nullabilité : contrainte à préciser
    CONSTRAINT pk_reclamation PRIMARY KEY (id_reclamation),
    CONSTRAINT fk_reclamation_etudiant FOREIGN KEY (id_etudiant)
        REFERENCES ETUDIANT (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_reclamation_gerant FOREIGN KEY (id_gerant)
        REFERENCES GERANT (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE REPONSE (
    id_reponse      INT          NOT NULL AUTO_INCREMENT,
    auteur          VARCHAR(50)  NOT NULL,
    contenu         TEXT         NOT NULL,
    statut          ENUM('Validée','Rejetée') NOT NULL DEFAULT 'Validée',
    id_reclamation  INT          NOT NULL,
    id_gerant       VARCHAR(8)   NOT NULL,
    CONSTRAINT pk_reponse PRIMARY KEY (id_reponse),
    CONSTRAINT fk_reponse_reclamation FOREIGN KEY (id_reclamation)
        REFERENCES RECLAMATION (id_reclamation) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reponse_gerant FOREIGN KEY (id_gerant)
        REFERENCES GERANT (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 7. TABLES DE JOINURE (associations N,N du MLD)
--    Clés primaires composées : aucune autre contrainte définie
--    dans le dictionnaire.
-- =====================================================================

-- Un étudiant peut jouer plusieurs fois au même jeu : le couple (étudiant, jeu)
-- ne suffit donc plus comme clé primaire. Chaque partie a son propre identifiant.
-- id_participation et date_participation sont à ajouter au MLD et au dictionnaire.
CREATE TABLE JOUER (
    id_participation    INT         NOT NULL AUTO_INCREMENT,
    id_etudiant         VARCHAR(8)  NOT NULL,
    id_jeu              VARCHAR(8)  NOT NULL,
    date_participation  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_jouer PRIMARY KEY (id_participation),
    CONSTRAINT fk_jouer_etudiant FOREIGN KEY (id_etudiant)
        REFERENCES ETUDIANT (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_jouer_jeu FOREIGN KEY (id_jeu)
        REFERENCES JEU (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Ligne de commande : une commande contient plusieurs plats (1,N) et un plat du
-- catalogue peut figurer dans plusieurs commandes (0,N). La quantité commandée
-- est propre à chaque ligne ; le stock global est dans PLAT.quantite.
-- Nouvelle table : à ajouter au MLD et au dictionnaire.
CREATE TABLE CONTENIR (
    id_commande  INT         NOT NULL,
    id_plat      VARCHAR(8)  NOT NULL,
    quantite     INT         NOT NULL,                                     -- quantité commandée : contrainte à préciser (minimum)
    CONSTRAINT pk_contenir PRIMARY KEY (id_commande, id_plat),
    CONSTRAINT fk_contenir_commande FOREIGN KEY (id_commande)
        REFERENCES COMMANDE (id_commande) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_contenir_plat FOREIGN KEY (id_plat)
        REFERENCES PLAT (id) ON DELETE RESTRICT ON UPDATE CASCADE          -- on garde l'historique des commandes
) ENGINE=InnoDB;

CREATE TABLE SELECTIONNER (
    id_etudiant  VARCHAR(8)  NOT NULL,
    id_favori    INT         NOT NULL,
    CONSTRAINT pk_selectionner PRIMARY KEY (id_etudiant, id_favori),
    CONSTRAINT fk_selectionner_etudiant FOREIGN KEY (id_etudiant)
        REFERENCES ETUDIANT (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_selectionner_favori FOREIGN KEY (id_favori)
        REFERENCES FAVORI (id_favori) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE BENEFICIER (
    id_etudiant   VARCHAR(8)  NOT NULL,
    id_promotion  INT         NOT NULL,
    CONSTRAINT pk_beneficier PRIMARY KEY (id_etudiant, id_promotion),
    CONSTRAINT fk_beneficier_etudiant FOREIGN KEY (id_etudiant)
        REFERENCES ETUDIANT (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_beneficier_promotion FOREIGN KEY (id_promotion)
        REFERENCES PROMOTION (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE REPONDRE (
    id_employe      VARCHAR(8)  NOT NULL,
    id_reclamation  INT         NOT NULL,
    CONSTRAINT pk_repondre PRIMARY KEY (id_employe, id_reclamation),
    CONSTRAINT fk_repondre_employe FOREIGN KEY (id_employe)
        REFERENCES EMPLOYE (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_repondre_reclamation FOREIGN KEY (id_reclamation)
        REFERENCES RECLAMATION (id_reclamation) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE CONSULTER (
    id_administrateur  VARCHAR(8)  NOT NULL,
    id_reclamation     INT         NOT NULL,
    CONSTRAINT pk_consulter PRIMARY KEY (id_administrateur, id_reclamation),
    CONSTRAINT fk_consulter_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_consulter_reclamation FOREIGN KEY (id_reclamation)
        REFERENCES RECLAMATION (id_reclamation) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ADMINISTRER (
    id_employe         VARCHAR(8)  NOT NULL,
    id_administrateur  VARCHAR(8)  NOT NULL,
    CONSTRAINT pk_administrer PRIMARY KEY (id_employe, id_administrateur),
    CONSTRAINT fk_administrer_employe FOREIGN KEY (id_employe)
        REFERENCES EMPLOYE (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_administrer_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ORGANISER (
    id_promotion       INT         NOT NULL,
    id_administrateur  VARCHAR(8)  NOT NULL,
    CONSTRAINT pk_organiser PRIMARY KEY (id_promotion, id_administrateur),
    CONSTRAINT fk_organiser_promotion FOREIGN KEY (id_promotion)
        REFERENCES PROMOTION (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_organiser_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PRODUIRE (
    id_administrateur  VARCHAR(8)  NOT NULL,
    id_reponse         INT         NOT NULL,
    CONSTRAINT pk_produire PRIMARY KEY (id_administrateur, id_reponse),
    CONSTRAINT fk_produire_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_produire_reponse FOREIGN KEY (id_reponse)
        REFERENCES REPONSE (id_reponse) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE GERER (
    id_administrateur  VARCHAR(8)  NOT NULL,
    id_plat            VARCHAR(8)  NOT NULL,
    CONSTRAINT pk_gerer PRIMARY KEY (id_administrateur, id_plat),
    CONSTRAINT fk_gerer_administrateur FOREIGN KEY (id_administrateur)
        REFERENCES ADMINISTRATEUR (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_gerer_plat FOREIGN KEY (id_plat)
        REFERENCES PLAT (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
