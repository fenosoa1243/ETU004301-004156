## Travaux à réaliser

### Initialisation du projet — ETU004301

- [ok] Installation de CodeIgniter 4
- [ok] Configuration de SQLite
- [ok] Configuration des routes
- [ok] Configuration de l'environnement
- [ok] Création du dépôt Git

### Base de données — ETU004301

- [ok] Conception de la base de données
- [ok] Création du fichier base.sql
- [ok] Création des tables
- [ok] Définition des clés étrangères
- [ok] Création des index
- [ok] Création des vues SQL
- [ok] Insertion des données initiales

### Espace Opérateur — ETU004301

#### Gestion des préfixes

- [ok] CRUD des préfixes
- [ok] Validation de l'unicité
- [ok] Activation/Désactivation

#### Gestion des types d'opérations

- [ok] CRUD des opérations
- [ok] Dépôt
- [ok] Retrait
- [ok] Transfert

#### Gestion des frais

- [ok] Création des tranches
- [ok] Modification des frais
- [ok] Calcul automatique selon le montant

#### Tableau de bord opérateur

- [ok] Situation des gains
- [ok] Situation des comptes clients
- [ok] Statistiques des opérations

### Espace Client — ETU004156

#### Authentification

- [ok] Connexion par numéro
- [ok] Création automatique du compte
- [ok] Vérification des préfixes

#### Gestion des opérations

- [ok] Consultation du solde
- [ok] Dépôt
- [ok] Retrait
- [ok] Transfert
- [ok] Historique des transactions

### Interface utilisateur — ETU004156

- [ok] Intégration de Bootstrap 5
- [ok] Responsive Design
- [ok] Dashboard
- [ok] Formulaires
- [ok] Tableaux
- [ok] Messages de validation

### Sécurité

- [ok] Validation des formulaires — ETU004156
- [ok] Protection CSRF — ETU004156
- [ok] Protection XSS — ETU004156
- [ok] Requêtes préparées — ETU004301
- [ok] Transactions SQLite — ETU004301

### Corrections et maintenance — ETU004301 / ETU004156

#### Base de données

- [ok] Correction erreur « no such table: clients » (base SQLite vide dans `writable/database.db`)
- [ok] Copie de la base peuplée vers le chemin utilisé par CodeIgniter
- [ok] Correction du trigger `tg_transactions_update_soldes` (dépôt créditait en débit)
- [ok] Mise à jour de `base.sql` avec le trigger corrigé (dépôt / retrait / transfert)

#### Espace Client

- [ok] Correction du dépôt côté client (solde crédité correctement après transaction)

### Assets locaux (hors Internet) — ETU004156

- [ok] Remplacement des CDN par des fichiers locaux dans `public/vendor`
- [ok] Bootstrap 5.3.3 local (`public/vendor/bootstrap-5.3.3/`)
- [ok] Bootstrap Icons 1.11.3 local (`public/vendor/bootstrap-icons/`)
- [ok] Chart.js 4.4.4 local (`public/vendor/chart.js/`)
- [ok] Extraction des styles inline vers `public/css/` (admin, client, client-guest)
- [ok] Création des sources SCSS dans `public/scss/`
- [ok] Mise à jour des layouts admin et client pour charger les assets locaux

### Version 2 — Planification (à réaliser)

#### Analyse

- [ok] Analyse fonctionnelle Version 2 (opérateurs externes, transferts inter-opérateurs)
- [ok] Définition de l'extension base de données (`operators`, `operator_prefixes`, commission, vues)
- [ok] Plan des modules admin et client à développer

#### Base de données

- [ok] Script SQL incrémental Version 2 (`base_v2.sql`)
- [ok] Tables `operators`, `operator_prefixes`, `inter_operator_commissions`, `commission_history`
- [ok] Extension table `transactions` (transferts inter-opérateurs)
- [ok] Nouvelles vues SQL (gains internes/externes, montants à envoyer)
- [ok] Adaptation du trigger pour transferts externes

#### Espace Opérateur — Version 2

- [ok] Module Opérateurs (CRUD)
- [ok] Module Préfixes des autres opérateurs (CRUD)
- [ok] Module Commission inter-opérateur (CRUD + historique)
- [ok] Adaptation Situation des gains (interne / externe)
- [ok] Page Montants à envoyer aux autres opérateurs
- [ok] Mise à jour du tableau de bord Version 2
- [ok] Page Rapports (filtres + architecture export PDF/Excel)

#### Services et logique métier — Version 2

- [ok] `OperatorDetectionService` (détection interne / externe)
- [ok] Extension `FeeService` (commission supplémentaire)
- [ok] Extension `OperationService` (transferts inter-opérateurs)
- [ok] Extension `StatisticsService` (stats V2)
- [ok] Validations métier supplémentaires

#### Espace Client — Version 2

- [ok] Adaptation du transfert (détection opérateur, frais détaillés)
- [ok] Affichage commission + commission supplémentaire sur le formulaire transfert
- [ok] Tests de régression transferts internes (comportement V1 inchangé)
