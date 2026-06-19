# PRD - Portail interne Néré Tools et premier module Timesheets

Version : 1.0  
Date : 19 juin 2026  
Produit : Néré Tools  
Domaine cible : `tools.nerecapital.com`  
Premier outil : Générateur de feuilles de temps, route `/timesheets`  
Statut : Spécification fonctionnelle et technique pour développement initial

## 1. Résumé exécutif

Néré Capital souhaite mettre en place un portail interne accessible à l’adresse `tools.nerecapital.com`, destiné à centraliser les applications opérationnelles développées pour les équipes internes. La page d’accueil du portail présentera une liste d’outils disponibles, avec des liens directs vers chaque module. Le premier module à développer est le générateur de feuilles de temps, accessible via `tools.nerecapital.com/timesheets`.

Le portail doit être hébergé sur l’environnement Infomaniak de Néré Capital, lorsque le plan disponible permet l’exécution d’une application Laravel complète. L’authentification doit s’appuyer sur les comptes Microsoft 365 de l’organisation, via Microsoft Entra ID. L’objectif est de ne pas créer un système d’identifiants parallèle, mais de s’inscrire dans l’environnement bureautique et documentaire existant : Microsoft 365, SharePoint, Outlook et, à terme, Teams.

Le projet doit être conçu comme une infrastructure interne légère, robuste, modulaire et évolutive. Le module Timesheets doit reprendre la logique déjà validée dans l’application Streamlit existante, mais sous forme d’un module Laravel intégré au portail.

## 2. Objectifs du projet

### 2.1 Objectif principal

Développer un portail interne sécurisé, hébergé sous le domaine Néré Capital, permettant aux utilisateurs autorisés de se connecter avec leur compte Microsoft 365, de consulter la liste des outils internes disponibles, puis d’utiliser le premier outil métier : le générateur automatique de feuilles de temps.

### 2.2 Objectifs opérationnels

- Remplacer le prototype Streamlit public par une application plus robuste, institutionnelle et contrôlée.
- Centraliser les futurs outils internes dans un portail unique.
- Utiliser l’authentification Microsoft 365 pour éviter la multiplication des mots de passe.
- Préparer une intégration progressive avec SharePoint pour le stockage documentaire.
- Permettre la génération des feuilles de temps en PDF, individuellement ou par lot.
- Garantir une logique claire de droits d’accès, de journalisation et de confidentialité.
- Poser une base technique réutilisable pour de futurs modules : reporting, génération documentaire, suivi portefeuille, administration interne.

## 3. Périmètre de la première version

### 3.1 Inclus dans la première version

La première version doit inclure :

- Une application Laravel déployable sur l’hébergement Infomaniak compatible.
- Le sous-domaine `tools.nerecapital.com` pointant vers l’application.
- Une page d’authentification Microsoft 365.
- Une page d’accueil présentant le catalogue des outils internes.
- Une carte active pour l’outil Timesheets.
- Des cartes inactives ou “bientôt disponible” pour les futurs outils.
- Le module `/timesheets` permettant de générer les feuilles de temps.
- La génération PDF conforme à la logique métier validée.
- Le téléchargement des PDFs et d’un fichier ZIP global.
- Un module d’administration minimal pour gérer les salariés, les coefficients et les règles de signature.
- Une journalisation minimale des générations.
- Une configuration prête pour une intégration SharePoint ultérieure ou partielle.

### 3.2 Hors périmètre de la première version

Les éléments suivants ne sont pas obligatoires dans la première version, sauf arbitrage contraire :

- Signature électronique juridiquement qualifiée.
- Workflow complet d’approbation.
- Notifications Teams.
- Archivage automatique obligatoire dans SharePoint.
- Synchronisation bidirectionnelle complète avec SharePoint Lists.
- Gestion avancée des habilitations par département.
- Application mobile dédiée.
- Tableau de bord analytique des temps passés.

### 3.3 Préparation pour les évolutions futures

Même si certaines fonctionnalités ne sont pas livrées immédiatement, le code doit être structuré pour permettre :

- l’ajout de nouveaux outils sous forme de modules ;
- la connexion à Microsoft Graph ;
- l’enregistrement automatique des fichiers dans SharePoint ;
- l’envoi d’emails via Outlook ;
- l’ajout d’un workflow de validation.

## 4. Utilisateurs cibles

### 4.1 Types d’utilisateurs

| Rôle | Description | Accès attendu |
|---|---|---|
| Administrateur | Personne chargée de configurer le portail, les outils et les utilisateurs | Tous les modules, administration, paramètres |
| Finance / DAF | Utilisateur responsable des feuilles de temps, coefficients et exports | Timesheets, administration Timesheets, consultation des générations |
| Direction | DG ou membres habilités | Consultation, génération, validation future |
| Manager | Responsable hiérarchique ou chef de projet | Consultation limitée, génération selon périmètre futur |
| Utilisateur standard | Collaborateur interne | Accès limité aux outils autorisés |

### 4.2 Règle d’accès initiale

Pour la première version, seuls les utilisateurs appartenant au tenant Microsoft 365 de Néré/I&P et explicitement autorisés dans la base de l’application doivent pouvoir accéder au portail.

Deux niveaux de contrôle sont donc requis :

1. Authentification Microsoft réussie.
2. Présence de l’adresse email dans la table des utilisateurs autorisés.

Cette double barrière évite qu’un compte invité ou un ancien collaborateur avec un accès résiduel puisse utiliser l’application sans autorisation applicative.

## 5. Parcours utilisateur

### 5.1 Connexion au portail

1. L’utilisateur ouvre `https://tools.nerecapital.com`.
2. S’il n’est pas connecté, il est redirigé vers Microsoft 365.
3. Il s’authentifie avec son compte professionnel.
4. Microsoft Entra ID renvoie l’utilisateur vers l’application.
5. L’application vérifie que l’email de l’utilisateur est autorisé.
6. L’utilisateur arrive sur la page d’accueil du portail.

### 5.2 Page d’accueil

La page d’accueil affiche :

- le logo Néré Capital ou I&P selon les actifs disponibles ;
- le nom de l’utilisateur connecté ;
- un titre clair, par exemple “Néré Tools” ;
- une courte phrase d’introduction ;
- une grille de cartes représentant les outils internes.

Exemple de cartes :

| Outil | Statut | Route |
|---|---|---|
| Feuilles de temps | Actif | `/timesheets` |
| Générateur de documents | Bientôt disponible | `/documents` |
| Reporting portefeuille | Bientôt disponible | `/reporting` |
| Administration | Selon rôle | `/admin` |

### 5.3 Accès au module Timesheets

1. L’utilisateur clique sur la carte “Feuilles de temps”.
2. Il est redirigé vers `tools.nerecapital.com/timesheets`.
3. Il sélectionne une période de génération.
4. Il sélectionne les salariés concernés.
5. Il vérifie les coefficients et paramètres.
6. Il lance la génération.
7. L’application produit les PDF et, si plusieurs fichiers sont générés, un ZIP.
8. L’utilisateur télécharge les fichiers.
9. L’application enregistre un log de génération.

### 5.4 Deep links

Le portail doit supporter les liens directs. Par exemple, si un utilisateur ouvre directement `https://tools.nerecapital.com/timesheets`, il doit :

- être redirigé vers Microsoft 365 s’il n’est pas connecté ;
- revenir automatiquement vers `/timesheets` après connexion ;
- voir une erreur propre si son compte n’a pas les droits requis.

## 6. Exigences fonctionnelles du portail

### 6.1 Authentification Microsoft 365

Le portail doit permettre la connexion via Microsoft Entra ID, en utilisant un flux OAuth 2.0 adapté aux applications web serveur.

Exigences :

- utiliser Microsoft 365 comme fournisseur d’identité ;
- ne pas stocker les mots de passe des utilisateurs ;
- récupérer au minimum l’email, le nom complet et l’identifiant Microsoft de l’utilisateur ;
- créer ou mettre à jour localement le profil utilisateur à la première connexion autorisée ;
- refuser l’accès aux utilisateurs non autorisés ;
- prévoir la déconnexion applicative ;
- protéger toutes les routes internes par middleware d’authentification.

Variables d’environnement attendues :

```env
MICROSOFT_TENANT_ID=
MICROSOFT_CLIENT_ID=
MICROSOFT_CLIENT_SECRET=
MICROSOFT_REDIRECT_URI=https://tools.nerecapital.com/auth/microsoft/callback
```

Routes attendues :

```text
GET /login
GET /auth/microsoft/redirect
GET /auth/microsoft/callback
POST /logout
```

### 6.2 Autorisation et rôles

Le système doit gérer les rôles suivants :

- `admin`
- `finance`
- `direction`
- `manager`
- `user`

Pour la première version, les rôles peuvent être gérés en base de données dans une table `users`.

Règle minimale :

| Route | Rôles autorisés |
|---|---|
| `/` | Tous les utilisateurs authentifiés et autorisés |
| `/timesheets` | `admin`, `finance`, `direction` |
| `/admin` | `admin` |
| `/admin/employees` | `admin`, éventuellement `finance` |

### 6.3 Catalogue des outils

La page d’accueil doit afficher les outils disponibles à partir d’une configuration ou d’une table dédiée.

Champs recommandés :

| Champ | Description |
|---|---|
| `name` | Nom affiché de l’outil |
| `slug` | Identifiant technique |
| `description` | Description courte |
| `route` | Route interne |
| `status` | `active`, `coming_soon`, `disabled` |
| `icon` | Icône facultative |
| `required_role` | Rôle minimal requis |
| `display_order` | Ordre d’affichage |

Exemple :

```php
[
    'name' => 'Feuilles de temps',
    'slug' => 'timesheets',
    'description' => 'Génération automatique des feuilles mensuelles en PDF.',
    'route' => '/timesheets',
    'status' => 'active',
    'required_role' => 'finance',
]
```

## 7. Exigences fonctionnelles du module Timesheets

### 7.1 Objectif du module

Le module Timesheets doit permettre de générer automatiquement des feuilles de temps mensuelles, sur une période donnée, pour un ou plusieurs salariés, avec une répartition analytique par programme ou projet.

### 7.2 Données nécessaires

Pour chaque salarié, l’application doit disposer des informations suivantes :

| Donnée | Obligatoire | Exemple |
|---|---|---|
| Prénom | Oui | Josias |
| Nom | Oui | DIAMITANI |
| Nom complet affiché | Calculé | Josias DIAMITANI |
| Entité | Oui | NERE CAPITAL PARTNERS |
| Lieu | Oui | Ouagadougou |
| Poste | Oui | Chargé de projet amorçage |
| Code analytique | Oui | 1.1.1 Personnel technique |
| Responsable de signature | Oui, sauf règle spéciale | Alida OUEDRAOGO |
| Titre du signataire | Oui | Responsable hiérarchique |
| IPAS | Oui | 0 |
| CATAL1.5 | Oui | 100 |
| IPDE | Oui | 0 |
| Autres projets | Calculé ou saisi | 80 |
| Actif | Oui | Oui |

### 7.3 Règles de période

L’utilisateur doit pouvoir choisir :

- une date de début ;
- une date de fin ;
- ou une période prédéfinie, par exemple T1, T2, S1, année complète.

Pour chaque mois compris dans la période, l’application doit générer une feuille mensuelle.

Exemples :

| Période choisie | Feuilles générées |
|---|---|
| 01/01/2026 au 31/03/2026 | Janvier, Février, Mars |
| 01/01/2026 au 30/06/2026 | Janvier à Juin |
| 01/04/2026 au 30/06/2026 | Avril, Mai, Juin |

### 7.4 Règle de découpage des semaines

Pour chaque mois, les semaines doivent être calculées ainsi :

1. La première semaine commence le premier jour du mois.
2. La première semaine se termine le premier dimanche du mois.
3. Les semaines suivantes vont du lundi au dimanche.
4. La dernière semaine se termine au dernier jour du mois, même si ce n’est pas un dimanche.

Exemple pour janvier 2026 :

| Semaine | Date de début | Date de fin |
|---|---|---|
| 1 | 01/01/2026 | 04/01/2026 |
| 2 | 05/01/2026 | 11/01/2026 |
| 3 | 12/01/2026 | 18/01/2026 |
| 4 | 19/01/2026 | 25/01/2026 |
| 5 | 26/01/2026 | 31/01/2026 |

### 7.5 Règle de date de signature

La date de signature doit correspondre au premier jour ouvré après la fin du mois.

Règle de base :

- exclure les samedis ;
- exclure les dimanches ;
- exclure les jours fériés configurés dans l’application.

Exemple :

| Mois | Dernier jour du mois | Date de signature attendue |
|---|---|---|
| Janvier 2026 | 31/01/2026, samedi | 02/02/2026, lundi |
| Février 2026 | 28/02/2026, samedi | 02/03/2026, lundi |
| Mars 2026 | 31/03/2026, mardi | 01/04/2026, mercredi |

### 7.6 Jours fériés

Le système doit permettre de gérer une liste de jours fériés.

Champs recommandés :

| Champ | Description |
|---|---|
| `date` | Date du jour férié |
| `name` | Nom du jour férié |
| `country` | Pays concerné, par défaut Burkina Faso |
| `active` | Oui/non |

La première version peut permettre une saisie simple en administration, sans synchronisation automatique avec un calendrier externe.

### 7.7 Coefficients analytiques

Chaque feuille doit afficher les colonnes analytiques suivantes par défaut :

- IPAS
- CATAL1,5°T
- IPDE

Pour certains profils, une colonne supplémentaire doit être affichée :

- Autres projets

Le total des coefficients doit toujours être égal à 100 %.

Validation obligatoire :

```text
IPAS + CATAL1,5°T + IPDE + Autres projets = 100 %
```

Si la colonne “Autres projets” n’est pas applicable, elle ne doit pas être affichée dans le PDF.

### 7.8 Règles particulières connues

#### 7.8.1 Cas Job ZONGO, DG

Pour Job ZONGO :

- la fiche doit être cosignée par la DAF, Germaine ;
- le libellé du bloc de droite doit être “Signature du DAF :” ;
- le nom du signataire doit être `BAKO/NAGALO A Germaine`, sauf mise à jour par l’administrateur ;
- la colonne “Autres projets” doit être affichée ;
- “Autres projets” doit être calculé pour que le total atteigne 100 %.

#### 7.8.2 Cas Germaine, DAF

Pour Germaine :

- la fiche doit afficher la colonne “Autres projets” ;
- “Autres projets” doit être calculé pour que le total atteigne 100 % ;
- le signataire doit être le DG, sauf configuration contraire.

#### 7.8.3 Règle générale de calcul “Autres projets”

```text
Autres projets = 100 - IPAS - CATAL1,5°T - IPDE
```

La valeur ne doit jamais être négative.

Si la valeur calculée est négative, l’application doit bloquer la génération et afficher un message d’erreur clair.

### 7.9 Génération PDF

Chaque feuille de temps mensuelle doit être générée au format PDF.

Le PDF doit contenir :

- le titre “FEUILLE DE TEMPS” ;
- l’entité ;
- le nom et prénom du salarié ;
- le lieu ;
- l’intitulé du poste ;
- le code analytique ;
- le mois ;
- l’année ;
- le tableau des semaines ;
- les colonnes analytiques applicables ;
- la ligne “Moyenne” ;
- le bloc de signature salarié ;
- le bloc de signature responsable, DAF ou DG selon le cas ;
- les dates de signature ;
- les noms complets des signataires.

### 7.10 Nommage des fichiers

Convention recommandée :

```text
Timesheet_YYYY_MM_Prenom_NOM.pdf
```

Exemples :

```text
Timesheet_2026_01_Josias_DIAMITANI.pdf
Timesheet_2026_06_Alida_OUEDRAOGO.pdf
```

Pour un export groupé :

```text
Timesheets_YYYY-MM-DD_to_YYYY-MM-DD.zip
```

Exemple :

```text
Timesheets_2026-01-01_to_2026-06-30.zip
```

### 7.11 Prévisualisation

La première version peut proposer une prévisualisation HTML simple avant génération, mais ce n’est pas obligatoire si la génération PDF est fiable.

Priorité :

1. génération correcte ;
2. validation des données ;
3. téléchargement ;
4. prévisualisation.

### 7.12 Historique des générations

À chaque génération, l’application doit enregistrer :

| Champ | Description |
|---|---|
| Utilisateur | Qui a lancé la génération |
| Date et heure | Horodatage |
| Période | Date début et date fin |
| Salariés | Liste ou nombre de salariés |
| Nombre de PDFs | Nombre généré |
| Statut | Succès ou erreur |
| Message d’erreur | Si applicable |
| Chemin local ou SharePoint | Si disponible |

## 8. Intégration Microsoft 365

### 8.1 Authentification

L’authentification Microsoft 365 est obligatoire dès la première version de production.

Le flux recommandé est OAuth 2.0 Authorization Code avec OpenID Connect, adapté à une application web serveur.

Scopes d’authentification minimum :

```text
openid
profile
email
User.Read
```

### 8.2 SharePoint

L’intégration SharePoint peut être livrée en deuxième incrément, mais l’architecture doit être prête dès le départ.

Objectifs SharePoint :

- enregistrer automatiquement les PDFs générés dans une bibliothèque documentaire ;
- créer une arborescence par année, mois et salarié ;
- récupérer éventuellement les données salariés depuis une SharePoint List ;
- éviter les pièces jointes lourdes par email en privilégiant les liens SharePoint.

Structure cible recommandée :

```text
SharePoint / Finance / Timesheets / 2026 / 01 - Janvier / Timesheet_2026_01_Josias_DIAMITANI.pdf
SharePoint / Finance / Timesheets / 2026 / 02 - Février / Timesheet_2026_02_Josias_DIAMITANI.pdf
```

Variables d’environnement recommandées :

```env
MS_GRAPH_TENANT_ID=
MS_GRAPH_CLIENT_ID=
MS_GRAPH_CLIENT_SECRET=
SHAREPOINT_SITE_ID=
SHAREPOINT_DRIVE_ID=
SHAREPOINT_TIMESHEETS_ROOT_FOLDER_ID=
```

Permissions Microsoft Graph à étudier :

```text
Files.ReadWrite
Sites.Selected, privilégié si possible
Sites.ReadWrite.All, uniquement si Sites.Selected n’est pas praticable
```

Le principe de moindre privilège doit être appliqué : l’application ne doit accéder qu’au site SharePoint et au dossier strictement nécessaires.

### 8.3 Outlook

L’envoi d’emails via Outlook n’est pas obligatoire en première version.

Évolution possible :

- envoyer un email au DAF après génération ;
- envoyer un lien SharePoint vers les fichiers ;
- envoyer une notification au salarié concerné ;
- éviter les pièces jointes lorsque SharePoint est disponible.

Permission Graph potentielle :

```text
Mail.Send
```

## 9. Architecture technique recommandée

### 9.1 Stack applicative

Stack recommandée :

| Composant | Choix recommandé |
|---|---|
| Framework | Laravel |
| Langage | PHP 8.2 ou version compatible avec l’hébergement |
| Base de données | MySQL ou MariaDB |
| Front-end | Blade + Vite, éventuellement Tailwind CSS |
| Auth Microsoft | Laravel Socialite ou package compatible Microsoft Entra ID |
| Appels Microsoft Graph | SDK Microsoft Graph PHP ou client HTTP Guzzle |
| PDF | DomPDF pour MVP, wkhtmltopdf si besoin de fidélité supérieure |
| ZIP | Extension PHP ZipArchive |
| Import Excel futur | PhpSpreadsheet ou Laravel Excel |
| Journalisation | Logs Laravel + table dédiée |

### 9.2 Hébergement

Le projet cible l’hébergement Infomaniak existant, sous réserve de compatibilité.

Pré-requis à vérifier dans le plan Infomaniak :

- PHP 8.2 ou supérieur, ou version compatible Laravel ;
- Composer disponible ;
- accès SSH ;
- base MySQL/MariaDB ;
- possibilité de pointer le sous-domaine vers le dossier `/public` de Laravel ;
- possibilité de configurer des variables d’environnement ;
- possibilité de créer des tâches cron ;
- extension PHP ZipArchive ;
- extension PHP GD ou Imagick si nécessaire ;
- limites de mémoire suffisantes pour générer plusieurs PDFs.

Docker n’est pas obligatoire si ces pré-requis sont satisfaits.

Docker devient utile uniquement si :

- l’hébergement mutualisé ne permet pas Laravel correctement ;
- la génération PDF exige un moteur externe comme Chrome Headless ;
- le projet évolue vers plusieurs services complexes ;
- une reproductibilité stricte des environnements devient nécessaire.

### 9.3 Structure de routes

Routes minimales :

```php
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/auth/microsoft/redirect', [MicrosoftAuthController::class, 'redirect'])->name('auth.microsoft.redirect');
Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'callback'])->name('auth.microsoft.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'authorized_user'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('/timesheets/generate', [TimesheetController::class, 'generate'])->name('timesheets.generate');
    Route::get('/timesheets/generations', [TimesheetGenerationController::class, 'index'])->name('timesheets.generations');
    Route::get('/timesheets/download/{generation}', [TimesheetDownloadController::class, 'download'])->name('timesheets.download');

    Route::middleware(['role:admin,finance'])->group(function () {
        Route::get('/admin/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/admin/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/admin/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/admin/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/admin/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    });
});
```

### 9.4 Services applicatifs

Créer des services dédiés pour isoler la logique métier :

```text
app/Services/Timesheets/TimesheetCalendarService.php
app/Services/Timesheets/TimesheetAllocationService.php
app/Services/Timesheets/TimesheetPdfService.php
app/Services/Timesheets/TimesheetZipService.php
app/Services/Microsoft/MicrosoftGraphService.php
app/Services/Microsoft/SharePointStorageService.php
```

Rôle des services :

| Service | Responsabilité |
|---|---|
| `TimesheetCalendarService` | Calcul des semaines et dates de signature |
| `TimesheetAllocationService` | Validation des coefficients, calcul “Autres projets” |
| `TimesheetPdfService` | Génération des PDF |
| `TimesheetZipService` | Assemblage des exports ZIP |
| `MicrosoftGraphService` | Appels génériques à Microsoft Graph |
| `SharePointStorageService` | Upload et classement des PDFs dans SharePoint |

## 10. Modèle de données

### 10.1 Table `users`

| Champ | Type | Description |
|---|---|---|
| `id` | bigint | Identifiant interne |
| `name` | string | Nom complet Microsoft |
| `email` | string unique | Email professionnel |
| `microsoft_id` | string nullable | Identifiant Microsoft |
| `role` | string | Rôle applicatif |
| `is_active` | boolean | Accès actif ou non |
| `last_login_at` | datetime nullable | Dernière connexion |
| `created_at` | timestamp | Création |
| `updated_at` | timestamp | Mise à jour |

### 10.2 Table `employees`

| Champ | Type | Description |
|---|---|---|
| `id` | bigint | Identifiant |
| `first_name` | string | Prénom |
| `last_name` | string | Nom |
| `display_name` | string | Nom affiché, calculable |
| `entity` | string | Entité |
| `location` | string | Lieu |
| `job_title` | string | Poste |
| `analytic_code` | string | Code analytique |
| `is_active` | boolean | Salarié actif |
| `requires_other_projects` | boolean | Afficher “Autres projets” |
| `signature_title` | string nullable | Titre du bloc signature droite |
| `signatory_name` | string nullable | Nom du signataire |
| `created_at` | timestamp | Création |
| `updated_at` | timestamp | Mise à jour |

### 10.3 Table `employee_allocations`

| Champ | Type | Description |
|---|---|---|
| `id` | bigint | Identifiant |
| `employee_id` | foreign key | Salarié |
| `effective_from` | date | Date de début d’application |
| `effective_to` | date nullable | Date de fin d’application |
| `ipas_pct` | decimal | IPAS |
| `catal_pct` | decimal | CATAL1,5°T |
| `ipde_pct` | decimal | IPDE |
| `other_projects_pct` | decimal nullable | Autres projets, calculable |
| `created_at` | timestamp | Création |
| `updated_at` | timestamp | Mise à jour |

### 10.4 Table `holidays`

| Champ | Type | Description |
|---|---|---|
| `id` | bigint | Identifiant |
| `date` | date | Date |
| `name` | string | Nom |
| `country` | string | Pays |
| `is_active` | boolean | Actif ou non |
| `created_at` | timestamp | Création |
| `updated_at` | timestamp | Mise à jour |

### 10.5 Table `timesheet_generations`

| Champ | Type | Description |
|---|---|---|
| `id` | bigint | Identifiant |
| `user_id` | foreign key | Utilisateur ayant généré |
| `period_start` | date | Début de période |
| `period_end` | date | Fin de période |
| `employee_count` | integer | Nombre de salariés |
| `pdf_count` | integer | Nombre de PDFs |
| `status` | string | `success`, `failed`, `partial` |
| `local_path` | string nullable | Chemin local ou storage |
| `sharepoint_url` | string nullable | URL SharePoint future |
| `error_message` | text nullable | Message d’erreur |
| `created_at` | timestamp | Création |
| `updated_at` | timestamp | Mise à jour |

### 10.6 Table `tools`

| Champ | Type | Description |
|---|---|---|
| `id` | bigint | Identifiant |
| `name` | string | Nom de l’outil |
| `slug` | string unique | Slug |
| `description` | text | Description |
| `route` | string | Route |
| `status` | string | `active`, `coming_soon`, `disabled` |
| `required_role` | string nullable | Rôle minimal |
| `display_order` | integer | Ordre |
| `created_at` | timestamp | Création |
| `updated_at` | timestamp | Mise à jour |

## 11. Interface utilisateur

### 11.1 Principes visuels

L’interface doit être sobre, claire et professionnelle. Elle doit éviter une esthétique trop “prototype” et se rapprocher d’un outil interne institutionnel.

Guidelines :

- utiliser une palette inspirée de la charte I&P ;
- privilégier les fonds clairs ;
- utiliser le brun I&P comme couleur institutionnelle principale ;
- utiliser l’orange I&P comme accent ;
- utiliser des cartes et composants épurés ;
- éviter les animations inutiles ;
- garantir une lisibilité élevée.

Couleurs suggérées :

| Usage | Couleur |
|---|---|
| Couleur institutionnelle principale | `#522400` ou `#532609` |
| Accent | `#e1580a` |
| Bleu secondaire | `#42707c` |
| Gris texte | `#5f5249` |
| Gris clair | `#cdc6c0` |
| Fond | `#f8f6f3` ou blanc |

Typographies :

- interface web : Work Sans si disponible ;
- fallback : Arial, Calibri, sans-serif ;
- titres : Cambria ou une police proche si l’actif Le Monde Livre n’est pas disponible.

### 11.2 Home page

La home page doit être composée de :

- un header avec logo et utilisateur connecté ;
- un titre “Néré Tools” ;
- une phrase d’introduction ;
- une grille responsive de cartes d’outils.

Exemple de contenu :

```text
Néré Tools
Bienvenue, [Nom utilisateur].
Sélectionnez un outil interne pour démarrer.
```

Carte Timesheets :

```text
Feuilles de temps
Générez automatiquement les feuilles mensuelles des collaborateurs, en PDF ou ZIP.
Bouton : Accéder
```

### 11.3 Page Timesheets

La page Timesheets doit inclure :

- un titre ;
- un court descriptif ;
- un formulaire de période ;
- une sélection de salariés ;
- un résumé des salariés sélectionnés ;
- un bouton de génération ;
- une zone de résultat avec les liens de téléchargement ;
- une zone d’erreurs ou alertes de validation.

### 11.4 Page Administration salariés

Fonctions minimales :

- lister les salariés ;
- créer un salarié ;
- modifier un salarié ;
- activer ou désactiver un salarié ;
- modifier les coefficients ;
- configurer les règles de signature.

## 12. Génération PDF et fidélité du rendu

### 12.1 Exigence de rendu

Le PDF doit être suffisamment fidèle aux feuilles de temps existantes :

- page A4 portrait ;
- titre centré ;
- tableau structuré ;
- blocs de signature en bas ;
- police lisible ;
- marges propres ;
- dates au format `jj/mm/aaaa` ;
- pourcentages avec symbole `%`.

### 12.2 Moteur PDF

Moteur initial recommandé : DomPDF.

Critères pour rester sur DomPDF :

- rendu acceptable ;
- compatibilité avec l’hébergement ;
- pas de dépendance externe lourde.

Critères pour passer à wkhtmltopdf ou un moteur plus avancé :

- rendu HTML/CSS insuffisant ;
- besoin de mise en page plus sophistiquée ;
- problèmes de pagination ;
- disponibilité du binaire sur l’hébergement.

## 13. Sécurité

### 13.1 Exigences générales

- Toutes les routes internes doivent être protégées.
- L’accès doit être réservé aux utilisateurs authentifiés Microsoft 365 et autorisés localement.
- Les secrets doivent être stockés dans `.env`, jamais dans Git.
- Les fichiers générés doivent être stockés temporairement ou dans un espace sécurisé.
- Les exports doivent être nettoyés automatiquement après une durée configurable si stockés localement.
- Les logs ne doivent jamais contenir de tokens, secrets ou mots de passe.
- Les erreurs utilisateur doivent être propres et non techniques.
- Les erreurs techniques doivent être journalisées côté serveur.

### 13.2 Données sensibles

Les données manipulées sont internes et potentiellement sensibles :

- noms de salariés ;
- postes ;
- coefficients analytiques ;
- signatures hiérarchiques ;
- fichiers PDF RH/finance.

Ces données ne doivent pas être exposées publiquement, indexées, ou stockées dans des dépôts Git.

### 13.3 Gestion des fichiers

Les fichiers générés localement doivent être placés dans un dossier non public, par exemple :

```text
storage/app/timesheets/
```

Ils ne doivent pas être placés directement dans `/public`.

Le téléchargement doit passer par un contrôleur Laravel qui vérifie les droits avant de servir le fichier.

### 13.4 Variables sensibles

Le fichier `.env` doit contenir les secrets, mais ne doit jamais être versionné.

`.gitignore` doit inclure :

```gitignore
.env
/storage/app/timesheets
/public/storage
/vendor
/node_modules
*.pdf
*.zip
```

## 14. Déploiement

### 14.1 Préparation du sous-domaine

Créer le sous-domaine :

```text
tools.nerecapital.com
```

Le document root doit pointer vers :

```text
/path/to/project/public
```

### 14.2 Déploiement Laravel

Étapes génériques :

```bash
git clone [repository]
cd [project]
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 14.3 Permissions dossiers

Les dossiers suivants doivent être accessibles en écriture par l’application :

```text
storage/
bootstrap/cache/
```

### 14.4 Tâches planifiées

Configurer le cron Laravel si nécessaire :

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Utilisation possible :

- nettoyage des exports temporaires ;
- synchronisation future avec SharePoint ;
- génération planifiée future.

### 14.5 Environnements

Prévoir au minimum :

| Environnement | Usage |
|---|---|
| Local | Développement |
| Staging | Tests avant mise en production, si possible |
| Production | `tools.nerecapital.com` |

Si un seul environnement est disponible au début, limiter la diffusion et tester soigneusement avant partage interne.

## 15. Configuration Microsoft Entra ID

### 15.1 App registration

Créer une application dans Microsoft Entra ID.

Paramètres attendus :

| Paramètre | Valeur |
|---|---|
| Nom | Néré Tools |
| Type | Web application |
| Redirect URI production | `https://tools.nerecapital.com/auth/microsoft/callback` |
| Redirect URI local | `http://localhost:8000/auth/microsoft/callback` |
| Supported account types | Comptes de l’organisation uniquement, sauf besoin contraire |

### 15.2 Secrets

Créer un client secret et l’enregistrer dans `.env`.

Ne jamais committer le secret.

### 15.3 Permissions initiales

Permissions minimales :

```text
openid
profile
email
User.Read
```

Permissions ultérieures pour SharePoint :

```text
Files.ReadWrite
Sites.Selected
```

Permission ultérieure pour Outlook :

```text
Mail.Send
```

### 15.4 Consentement administrateur

Certaines permissions Microsoft Graph peuvent nécessiter un consentement administrateur. Le développeur doit documenter clairement les permissions demandées et justifier chacune d’elles.

## 16. Tests et critères d’acceptation

### 16.1 Authentification

Critères :

- un utilisateur non connecté est redirigé vers Microsoft 365 ;
- un utilisateur connecté mais non autorisé localement reçoit un refus propre ;
- un utilisateur autorisé accède à la home page ;
- le nom de l’utilisateur est affiché ;
- la déconnexion fonctionne ;
- les deep links fonctionnent après connexion.

### 16.2 Home page

Critères :

- la page affiche le catalogue des outils ;
- la carte “Feuilles de temps” est active ;
- les cartes futures sont visibles mais non accessibles, ou affichent “Bientôt disponible” ;
- le design est sobre, responsive et lisible ;
- seuls les outils autorisés sont cliquables pour l’utilisateur.

### 16.3 Module Timesheets

Critères :

- l’utilisateur peut choisir une période ;
- l’utilisateur peut sélectionner un ou plusieurs salariés ;
- l’application calcule correctement les semaines ;
- l’application calcule correctement la date de signature ;
- l’application applique les coefficients ;
- l’application bloque si le total des coefficients est invalide ;
- l’application ajoute “Autres projets” pour Job et Germaine ;
- l’application applique la règle de signature de Job ;
- l’application génère les PDFs attendus ;
- l’application génère un ZIP si plusieurs PDFs sont créés ;
- les noms de fichiers respectent la convention ;
- la génération est enregistrée dans l’historique.

### 16.4 Sécurité

Critères :

- aucune route interne n’est accessible sans login ;
- les PDFs ne sont pas directement accessibles par URL publique ;
- les fichiers `.env`, PDF et ZIP ne sont pas versionnés ;
- les erreurs ne révèlent pas les secrets ;
- les rôles sont correctement appliqués.

## 17. Données de départ à prévoir

### 17.1 Utilisateurs applicatifs

Prévoir au moins :

| Email | Nom | Rôle | Actif |
|---|---|---|---|
| à renseigner | Administrateur initial | admin | Oui |

### 17.2 Outils

Seed initial :

| Nom | Route | Statut |
|---|---|---|
| Feuilles de temps | `/timesheets` | active |
| Générateur de documents | `/documents` | coming_soon |
| Reporting portefeuille | `/reporting` | coming_soon |

### 17.3 Salariés

Les salariés doivent être saisis via l’administration ou importés depuis un fichier CSV initial.

Champs minimum CSV :

```csv
first_name,last_name,entity,location,job_title,analytic_code,ipas_pct,catal_pct,ipde_pct,requires_other_projects,signature_title,signatory_name,is_active
```

## 18. Gestion des erreurs

### 18.1 Erreurs utilisateur

Exemples de messages :

| Situation | Message recommandé |
|---|---|
| Aucun salarié sélectionné | Veuillez sélectionner au moins un salarié. |
| Période invalide | La date de fin doit être postérieure à la date de début. |
| Coefficients invalides | Le total des coefficients doit être égal à 100 %. |
| Utilisateur non autorisé | Votre compte est authentifié, mais il n’est pas autorisé à accéder à ce portail. |
| PDF non généré | Une erreur est survenue pendant la génération. Veuillez réessayer ou contacter l’administrateur. |

### 18.2 Erreurs techniques

Les erreurs techniques doivent être loggées avec :

- utilisateur ;
- route ;
- action ;
- message d’erreur ;
- trace technique côté serveur ;
- horodatage.

Le détail technique ne doit pas être exposé à l’utilisateur final en production.

## 19. Performance

### 19.1 Volumétrie attendue

Hypothèse initiale :

- moins de 100 utilisateurs ;
- moins de 100 salariés ;
- générations ponctuelles mensuelles ou trimestrielles ;
- quelques dizaines de PDFs par génération.

### 19.2 Exigences

- La home page doit charger en moins de 2 secondes dans des conditions normales.
- Une génération de 50 PDFs doit rester raisonnable et ne pas provoquer de timeout serveur.
- Si la génération groupée devient lourde, prévoir une queue Laravel.

## 20. Qualité, maintenance et documentation

### 20.1 Documentation développeur

Le dépôt doit contenir :

- `README.md` : installation locale, configuration, déploiement ;
- `.env.example` : variables requises sans secrets ;
- `prd.md` : présent document ;
- documentation courte sur Microsoft Entra ID ;
- documentation courte sur les règles Timesheets.

### 20.2 Standards de code

- Utiliser des noms explicites.
- Isoler la logique métier dans des services.
- Éviter les calculs métier directement dans les contrôleurs.
- Prévoir des tests unitaires pour les services de calendrier et de coefficients.
- Prévoir des tests fonctionnels pour les routes critiques.

### 20.3 Tests unitaires prioritaires

Tests à écrire en priorité :

- calcul des semaines pour différents mois ;
- calcul de la date de signature ;
- exclusion des week-ends ;
- exclusion des jours fériés ;
- calcul “Autres projets” ;
- validation total 100 % ;
- règle spéciale Job ;
- règle spéciale Germaine.

## 21. Roadmap recommandée

### Phase 0 : cadrage technique

- Vérifier les capacités réelles du plan Infomaniak.
- Vérifier PHP, Composer, MySQL, SSH, cron, extensions PHP.
- Créer le dépôt Git privé.
- Créer le sous-domaine ou prévoir un environnement de test.

### Phase 1 : socle Laravel

- Installer Laravel.
- Configurer base de données.
- Créer les modèles et migrations.
- Créer la home page.
- Créer le catalogue des outils.
- Mettre en place le design de base.

### Phase 2 : authentification Microsoft

- Créer l’app registration Microsoft Entra ID.
- Implémenter la connexion Microsoft.
- Gérer les utilisateurs autorisés.
- Protéger les routes.
- Tester les deep links.

### Phase 3 : module Timesheets

- Migrer la logique métier de Streamlit vers Laravel.
- Créer l’administration salariés.
- Créer le formulaire de génération.
- Générer les PDFs.
- Générer les ZIP.
- Ajouter l’historique.
- Tester les cas particuliers.

### Phase 4 : durcissement production

- Nettoyage des fichiers temporaires.
- Logs propres.
- Vérification des rôles.
- Tests de génération groupée.
- Mise en production limitée.

### Phase 5 : intégration SharePoint

- Connecter Microsoft Graph.
- Enregistrer les PDFs dans SharePoint.
- Ajouter les liens SharePoint dans l’historique.
- Préparer l’envoi de notifications Outlook.

## 22. Risques et points de vigilance

| Risque | Impact | Mitigation |
|---|---|---|
| Plan Infomaniak insuffisant | Déploiement bloqué | Vérifier pré-requis avant développement complet |
| Auth Microsoft mal configurée | Connexion impossible | Tester d’abord en local avec app registration dédiée |
| PDF mal rendu avec DomPDF | Perte de fidélité | Prévoir moteur alternatif si nécessaire |
| Secrets exposés dans Git | Risque sécurité élevé | `.gitignore`, revue de dépôt, variables serveur |
| Permissions Graph trop larges | Risque de gouvernance | Appliquer le moindre privilège |
| Fichiers PDF publics | Risque confidentialité | Stocker dans `storage/app`, servir via contrôleur sécurisé |
| Complexité excessive dès le départ | Retard projet | Livrer par incréments |

## 23. Décisions structurantes

Décisions à acter avant développement :

1. Confirmer que Laravel est le framework cible.
2. Confirmer que Docker n’est pas obligatoire pour la première version.
3. Confirmer le plan Infomaniak et ses pré-requis.
4. Confirmer le domaine `tools.nerecapital.com`.
5. Confirmer l’usage obligatoire de Microsoft 365 pour l’authentification.
6. Confirmer si SharePoint est requis dès la première livraison ou en phase 5.
7. Confirmer les rôles initiaux et les premiers utilisateurs autorisés.
8. Confirmer les actifs graphiques à utiliser : logo Néré, logo I&P, palette, typographies.

## 24. Livrables attendus

À la fin de la première partie, le développeur doit livrer :

- une application Laravel fonctionnelle ;
- un dépôt Git propre et privé ;
- un fichier `.env.example` complet ;
- les migrations et seeders ;
- une home page catalogue ;
- une authentification Microsoft fonctionnelle ;
- le module Timesheets fonctionnel ;
- la génération PDF ;
- le téléchargement ZIP ;
- l’administration minimale des salariés ;
- l’historique des générations ;
- une documentation de déploiement ;
- une checklist de sécurité ;
- une note sur les étapes SharePoint suivantes.

## 25. Critère de succès final

La première partie du projet sera considérée comme réussie si un utilisateur autorisé peut :

1. ouvrir `https://tools.nerecapital.com` ;
2. se connecter avec son compte Microsoft 365 ;
3. arriver sur une home page propre listant les outils internes ;
4. cliquer sur “Feuilles de temps” ;
5. choisir une période et des salariés ;
6. générer les feuilles de temps en PDF ;
7. télécharger les fichiers individuellement ou en ZIP ;
8. constater que les règles métier sont respectées ;
9. retrouver l’opération dans l’historique ;
10. le tout sans exposer de données sensibles publiquement.

## 26. Références techniques utiles

- Microsoft identity platform, OAuth 2.0 Authorization Code Flow : https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-auth-code-flow
- Microsoft Graph, fichiers SharePoint et driveItems : https://learn.microsoft.com/en-us/graph/api/driveitem-list-children
- Microsoft Graph, envoi d’emails avec sendMail : https://learn.microsoft.com/en-us/graph/api/user-sendmail
- Laravel documentation : https://laravel.com/docs
- Infomaniak support, hébergement web : https://www.infomaniak.com/en/support/faq/admin2/web-hosting

