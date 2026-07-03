# Nere Tools

Portail interne Laravel pour NERE Capital.

L'application couvre aujourd'hui deux usages metier:

- generation de feuilles de temps PDF puis archivage ZIP ;
- gestion des conges avec demande, validation, solde et historique.

## Analyse rapide du code

Le projet est une application Laravel 13 assez classique cote structure, avec
une separation simple entre HTTP, logique metier et rendu.

Points notables:

- authentification Microsoft 365 via OAuth dans `app/Http/Controllers/Auth/MicrosoftAuthController.php` ;
- controle d'acces par role et par outil via `User::canAccessTool()` et le middleware `tool:*` ;
- logique metier placee surtout dans `app/Services` ;
- generation PDF faite maison pour limiter les dependances :
  - timesheets dans `app/Modules/Timesheets/Support/TimesheetPdfRenderer.php` ;
  - conges dans `app/Services/Leaves/LeavePdfService.php` ;
- archivage ZIP des feuilles de temps via `ZipArchive` ;
- routes web decoupees par domaine dans `routes/web/*.php` ;
- front leger avec Blade + Vite + Tailwind CSS 4 + un peu de JavaScript modulaire ;
- couverture de tests deja presente pour auth Microsoft, timesheets et conges.

## Organisation du depot

```text
app/
  Http/
    Controllers/       Pages web et actions HTTP
    Middleware/        Controle d'acces
  Models/              Entites Eloquent
  Modules/
    Timesheets/Support/ PDF/CSV/ZIP du module feuilles de temps
  Services/
    Leaves/            Logique metier du module conges
    TimesheetService.php
bootstrap/
config/
database/
  migrations/          Schema
  seeders/             Donnees de base
public/                Point d'entree web + assets de marque
resources/
  css/                 Shell UI + styles par module
  js/                  Boot global + scripts par module
  views/               Templates Blade
routes/
  web/                 Fichiers de routes par domaine
scripts/               Aides au HTTPS local et bundle CA
tests/
  Feature/             Parcours applicatifs
  Unit/                Services et logique pure
```

## Modules existants

### 1. Dashboard et administration

- `/` : dashboard qui liste les outils actifs.
- `/admin` : entree administration.
- `/admin/users` : gestion des utilisateurs.
- `/admin/conges` : administration du module conges.

### 2. Timesheets

Routes sous `/timesheets`.

Ce module permet:

- de generer des feuilles de temps depuis une selection de collaborateurs ;
- d'importer un CSV puis corriger les lignes avant generation ;
- de produire des PDF en stockage local ;
- de telecharger un ZIP des fichiers generes ;
- de consulter un historique des generations.

Le coeur du module est ici:

- `app/Services/TimesheetService.php`
- `app/Modules/Timesheets/Support/TimesheetPdfRenderer.php`
- `app/Modules/Timesheets/Support/TimesheetCsvParser.php`
- `app/Modules/Timesheets/Support/TimesheetArchiveBuilder.php`

### 3. Conges

Routes sous `/conges`.

Ce module permet:

- de soumettre une demande ;
- de suivre son historique ;
- de valider ou rejeter une demande ;
- de calculer les soldes ;
- de generer un PDF de decision ;
- d'envoyer des emails de notification.

Le coeur du module est ici:

- `app/Services/Leaves/LeaveRequestWorkflowService.php`
- `app/Services/Leaves/LeaveBalanceService.php`
- `app/Services/Leaves/LeaveDayCountService.php`
- `app/Services/Leaves/LeaveNotificationService.php`
- `app/Services/Leaves/LeavePdfService.php`

### Visa electronique et verification des conges

Les demandes de conge approuvees produisent desormais un document PDF final
avec visa electronique interne Nere Tools.

Le mecanisme repose sur:

- une reference unique du type `NC-CONGES-YYYY-00001` ;
- un token de verification aleatoire ;
- un QR code pointant vers la page de verification ;
- une empreinte SHA-256 du PDF final ;
- un stockage prive du fichier ;
- une page publique de verification par lien ou QR code ;
- une verification complementaire par upload du PDF exact ;
- des logs d'audit pour generation, consultation, verification, revocation et regeneration.

Routes principales ajoutees:

- `GET /conges/verify`
- `GET /conges/verify/{token}`
- `POST /conges/verify/upload`
- `GET /conges/{leaveRequest}/document`
- `POST /admin/conges/documents/{document}/revoke`
- `POST /admin/conges/{leaveRequest:uuid}/regenerate-document`

Fichiers clefs:

- `app/Services/Leaves/LeavePdfRenderer.php`
- `app/Services/Leaves/LeavePdfService.php`
- `app/Services/Leaves/LeaveDocumentReferenceService.php`
- `app/Http/Controllers/Leaves/LeaveDocumentVerificationController.php`
- `app/Http/Controllers/Leaves/Admin/LeaveDocumentAdminController.php`
- `database/migrations/2026_06_23_130001_upgrade_leave_documents_for_verification.php`

Stockage:

- les PDFs sont ecrits sur le disque `local` ;
- dans ce projet, `local` pointe vers `storage/app/private` ;
- les documents de conges finaux sont ranges sous `storage/app/private/leave-documents/YYYY/`.

## Prerequis

- PHP 8.4+
- Composer
- Node.js 20+ et npm
- extension PHP `zip` pour la generation des archives
- SQLite par defaut, ou une autre base si vous adaptez `.env`

## Configuration locale

1. Installer les dependances PHP et JS:

```bash
composer install
npm install
```

2. Initialiser l'environnement:

```bash
copy .env.example .env
```

3. Verifier que la base SQLite existe:

```bash
if not exist database\database.sqlite type nul > database\database.sqlite
```

4. Generer la cle application puis migrer et peupler:

```bash
php artisan key:generate
php artisan migrate --seed
```

Si le code du module conges est deja a jour mais que la base locale ne l'est
pas, applique au minimum:

```bash
php artisan migrate
```

Sans cette etape, la validation d'une demande approuvee peut echouer avec une
erreur SQL sur les colonnes documentaires de `leave_documents`.

## Auth Microsoft et variables utiles

Le projet attend une connexion Microsoft 365.

Variables importantes dans `.env`:

- `MICROSOFT_TENANT_ID`
- `MICROSOFT_CLIENT_ID`
- `MICROSOFT_CLIENT_SECRET`
- `MICROSOFT_REDIRECT_URI`
- `MICROSOFT_SCOPES`
- `MICROSOFT_CA_BUNDLE`
- `INITIAL_ADMIN_EMAIL`
- `INITIAL_ADMIN_NAME`

Le comportement actuel est le suivant:

- un utilisateur deja present dans `users` peut se connecter ;
- sinon, si son email existe dans `employees`, un compte local est cree au premier login ;
- sinon l'acces est refuse.

## Lancer le projet

### Option recommandee: HTTPS local

C'est le chemin principal a utiliser pour un vrai test applicatif, parce que:

- `.env.example` pointe vers `https://localhost:8443` ;
- `SESSION_SECURE_COOKIE=true` ;
- la redirection Microsoft est configuree en HTTPS local.

Preparation:

```bash
npm run setup:https
npm run setup:ca
```

Demarrage:

```bash
npm run serve:https
```

Services demarres:

- Laravel sur `http://127.0.0.1:8000`
- worker de queue Laravel pour traiter les notifications et jobs
- Vite en dev
- proxy HTTPS local sur `https://localhost:8443`

### Option simple: dev HTTP

```bash
composer run dev
```

Cette option est pratique pour du developpement front/back rapide, mais elle
n'est pas le meilleur choix pour tester l'auth Microsoft ou la session telle
qu'elle est configuree par defaut.

## Tests

```bash
php artisan test
```

Les tests couvrent deja:

- l'authentification Microsoft ;
- les acces aux modules ;
- le workflow conges ;
- la generation timesheets et CSV ;
- la generation du PDF final de conge ;
- la verification par token ;
- la verification par upload PDF ;
- les droits d'acces au document ;
- la revocation et la regeneration admin.

Verification conseillee apres modification:

```bash
php artisan migrate
php artisan test
npm run build
```

## Monitoring Laravel Pulse

Laravel Pulse est installe pour le monitoring applicatif interne.

Installation et mise a jour des dependances:

```bash
composer require laravel/pulse
php artisan vendor:publish --tag=pulse-config --tag=pulse-migrations
php artisan migrate
```

En deploiement:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

Le dashboard est disponible sur `/pulse`. Il passe par l'authentification Laravel
et par la Gate `viewPulse`; seuls les utilisateurs actifs avec le role `admin`
ou `finance` peuvent y acceder.

Pulse reste configure avec le stockage base de donnees par defaut:

```env
PULSE_ENABLED=true
PULSE_PATH=pulse
PULSE_STORAGE_DRIVER=database
PULSE_INGEST_DRIVER=storage
PULSE_STORAGE_KEEP="7 days"
```

Pour desactiver l'enregistrement Pulse en production, mettre:

```env
PULSE_ENABLED=false
```

Pour restreindre l'URL sans changer le code, modifier `PULSE_PATH` dans `.env`
puis vider le cache de configuration/routes si necessaire.

### Notes hebergement mutualise

Aucun process manager, Docker, Supervisor ou systemd n'est requis. Pulse ingere
les donnees pendant le cycle de vie des requetes/commandes Laravel.

Si l'hebergement propose deja une tache cron, les commandes optionnelles
compatibles sont:

```bash
php artisan pulse:check
php artisan queue:work --stop-when-empty
```

`pulse:work` est utile avec un worker persistant, mais il n'est pas requis pour
cette installation mutualisee.

## Stockage et fichiers generes

Les fichiers applicatifs sont stockes sur le disque `local`, qui pointe vers:

```text
storage/app/private
```

On y retrouve notamment:

- les PDF de conges ;
- les documents verifies `leave-documents/YYYY/*.pdf` ;
- les PDF de feuilles de temps ;
- les archives ZIP ;
- le bundle CA telecharge pour Microsoft si vous utilisez `npm run setup:ca`.

## Deploiement

Le depot ne contient pas de Dockerfile, pipeline CI/CD, config Nginx/Apache,
ou script d'infra de production. Le deploiement actuel est donc a traiter
comme un deploiement Laravel standard.

### Procedure minimale de deploiement

1. Provisionner un serveur avec:

- PHP 8.4+
- Composer
- Node.js 20+ pour builder les assets
- extension PHP `zip`
- un serveur web pointant vers `public/`

2. Recuperer le code puis installer les dependances:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

3. Configurer `.env` en production:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://...`
- variables base de donnees
- variables mail
- variables Microsoft 365

4. Initialiser Laravel:

```bash
php artisan key:generate
php artisan migrate --force --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Donner les droits d'ecriture a:

- `storage/`
- `bootstrap/cache/`

6. Sur hebergement mutualise, traiter la queue par cron si vous gardez la queue `database` en production:

```bash
php artisan queue:work --stop-when-empty
```

## Limites et observations utiles

- Pas d'automatisation de deploiement dans le depot.
- PDF construits sans librairie externe: simple et suffisant, mais a surveiller si les besoins d'impression deviennent plus riches.
- Le module timesheets est deja bien isole fonctionnellement via `app/Modules/Timesheets/Support`.
- Le module conges reste organise surtout par services, ce qui est coherent avec son etat actuel.
- La verification publique n'expose qu'un sous-ensemble d'informations pour eviter de divulguer des donnees RH sensibles.
- La verification par upload repose sur le hash du fichier exact: un PDF modifie, recomprime ou regenere differemment sera signale comme inconnu meme s'il derive d'un document legitime.
- Le fichier `README` d'origine Laravel ne decrivait pas l'application ; il a ete remplace par une doc projet.
