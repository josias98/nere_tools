# PRD - Module Demandes de congé - Néré Tools

Version : 1.0  
Date : 22 juin 2026  
Produit : Néré Tools  
Module : Demandes de congé  
Route cible : `/conges`  
Application cible : portail Laravel existant `tools.nerecapital.com`  
Statut : spécification fonctionnelle et technique pour intégration au code existant

## 1. Résumé exécutif

Néré Capital souhaite ajouter un second outil au portail interne Néré Tools : un module de gestion des demandes de congé. Ce module doit permettre aux collaborateurs de se connecter avec leur compte Microsoft 365, de consulter leur solde de congés, de soumettre une demande pour une période donnée, puis de suivre le statut de cette demande.

Le module doit permettre aux validateurs désignés de recevoir une notification, d’examiner la demande, de vérifier le solde disponible, de tenir compte d’éventuelles contraintes administratives ou périodes sensibles, puis d’approuver ou de rejeter la demande. Après validation, le système doit générer un document PDF de demande ou de situation de congé et l’archiver dans un dossier SharePoint privé dédié.

L’objectif principal est de remplacer progressivement le suivi manuel par fichier Excel par une source de vérité applicative, structurée, traçable et connectée à Microsoft 365. L’ancien état des congés doit servir de base historique initiale à importer dans la base de données, mais la source RH de vérité pour les collaborateurs actifs est la liste récente fournie par l’équipe Néré.

Le module doit être ajouté à l’application Laravel existante, sans créer une application séparée. Il doit réutiliser le socle déjà présent : authentification Microsoft 365, rôles applicatifs, catalogue des outils, design system Néré, routes protégées, base de données Laravel, stockage sécurisé et logique d’évolution vers SharePoint et Outlook.

## 2. Contexte applicatif existant

Le code actuel correspond à une application Laravel appelée Néré Tools. Elle sert de portail interne et contient déjà un premier module métier : `Feuilles de temps`, accessible via `/timesheets`.

### 2.1 Éléments existants à préserver

L’agent AI ou le développeur doit impérativement préserver les éléments suivants :

| Élément | État actuel | Règle pour le module Congés |
|---|---|---|
| Authentification Microsoft 365 | Déjà présente via `MicrosoftAuthController` | Réutiliser, ne pas dupliquer |
| Rôles utilisateurs | `admin`, `finance`, `direction`, `manager`, `user` | Étendre si nécessaire, sans casser l’existant |
| Catalogue des outils | Table `tools` et dashboard | Ajouter une carte active “Demandes de congé” |
| Module Timesheets | Routes, contrôleur, service, vues et tests | Ne pas modifier sauf nécessité de mutualisation |
| Design system | Classes `nc-*`, palette Néré, assets de marque | Réutiliser strictement |
| Stockage privé | `storage/app/private` | Utiliser pour documents temporaires Congés |
| Tests | PHPUnit déjà en place | Ajouter des tests Congés sans supprimer les tests existants |

### 2.2 Fichiers existants importants

Le module Congés devra s’intégrer autour de ces fichiers et dossiers :

```text
routes/web.php
app/Models/User.php
app/Models/Employee.php
app/Models/Tool.php
app/Http/Controllers/Auth/MicrosoftAuthController.php
app/Http/Middleware/EnsureUserHasRole.php
resources/views/layouts/app.blade.php
resources/views/dashboard.blade.php
resources/css/app.css
database/seeders/DatabaseSeeder.php
```

Le module Timesheets existant doit rester fonctionnel. Toute modification globale doit être testée contre les tests existants.

## 3. Objectifs du module Congés

### 3.1 Objectif principal

Développer un module Laravel intégré à Néré Tools permettant de gérer le cycle complet d’une demande de congé : soumission, notification, revue, validation ou rejet, mise à jour du solde, génération documentaire, archivage et historique.

### 3.2 Objectifs opérationnels

- Permettre à chaque collaborateur de consulter son solde de congés.
- Permettre à chaque collaborateur actif de soumettre une demande de congé.
- Calculer automatiquement le nombre de jours demandés en jours calendaires.
- Calculer les droits à raison de 2,5 jours par mois.
- Afficher le solde disponible, les jours en attente et le solde projeté.
- Notifier les validateurs via Outlook / Office 365 lorsqu’une demande est soumise.
- Permettre aux validateurs de valider ou rejeter une demande depuis l’application.
- Notifier le demandeur après validation ou rejet.
- Générer un document PDF après validation.
- Stocker le document généré dans un dossier SharePoint privé, avec un fallback local sécurisé si SharePoint n’est pas encore disponible.
- Permettre au Super Admin de configurer les validateurs sans modification du code.
- Importer l’historique initial depuis l’ancien fichier Excel de suivi.
- Ajouter un historique consultable par collaborateur et par les validateurs autorisés.

## 4. Périmètre

### 4.1 Inclus dans le MVP

La première version du module doit inclure :

| Fonctionnalité | Inclusion MVP |
|---|---|
| Carte “Demandes de congé” sur le dashboard | Oui |
| Route principale `/conges` | Oui |
| Connexion via Microsoft 365 | Oui, via socle existant |
| Consultation du solde personnel | Oui |
| Formulaire de demande de congé | Oui |
| Calcul automatique en jours calendaires | Oui |
| Contrôle du solde disponible | Oui |
| Workflow de soumission | Oui |
| File de validation pour Germaine, Gloria et Job | Oui |
| Validateurs configurables par Super Admin | Oui |
| Validation ou rejet avec commentaire | Oui |
| Notification email aux validateurs | Oui |
| Notification email au demandeur | Oui |
| Génération PDF après validation | Oui |
| Archivage SharePoint privé | Oui si les accès Graph sont disponibles ; fallback local sinon |
| Historique personnel | Oui |
| Historique global pour validateurs et admins | Oui |
| Import de l’ancien état Excel | Oui |
| Tests unitaires et fonctionnels | Oui |

### 4.2 Hors périmètre du MVP

Les éléments suivants sont exclus de la première version, sauf arbitrage contraire :

| Fonctionnalité | Raison |
|---|---|
| Signature électronique juridiquement qualifiée | Complexité juridique et technique excessive pour MVP |
| Validation multi-niveaux obligatoire | À prévoir en évolution après usage réel |
| App mobile dédiée | Non nécessaire pour une équipe d’environ quinze personnes |
| Synchronisation complète avec Outlook Calendar | Utile mais à placer en phase 2 |
| Gestion complexe de congés maladie et justificatifs | Peut être ajouté ultérieurement |
| Approbation directe depuis le corps de l’email | Risque et complexité inutiles au départ |
| Paie ou intégration comptable | Hors périmètre |

### 4.3 Préparation pour évolutions futures

Le code doit être conçu pour permettre plus tard :

- la création automatique d’un événement dans un calendrier Outlook partagé “Absences Néré” ;
- la synchronisation des documents avec une bibliothèque SharePoint RH ;
- l’ajout d’un workflow à deux niveaux ;
- l’ajout d’un tableau de bord RH ;
- l’export mensuel des absences ;
- la gestion de types d’absences plus fins.

## 5. Source de vérité RH

### 5.1 Collaborateurs actifs

La source de vérité RH pour les collaborateurs actifs est la liste récente ci-dessous. Elle prime sur l’ancien fichier Excel.

| Collaborateur | Poste | Département |
|---|---|---|
| BAKO / NAGALO Germaine | Directrice Administrative et Financière | Administratif et Finance |
| ZIO / SAVADOGO Aïcha | Directrice pôle études et conseils | Conseil |
| SANOU Aboubacar Sidiki | Responsable d'investissement | Equity |
| SOUBEIGA Brice Gaël | Directeur d'investissement | Equity |
| TRAORE Samiratou Cyrielle | Chargée d'investissement | Equity |
| DIAMITANI Josias Mansour | Chargé de projet | Accélération |
| OUEDRAOGO Fabien | Chauffeur-coursier | Administratif et Finance |
| OUEDRAOGO Alida | Responsable d'Amorçage | Accélération |
| OUEDRAOGO Relwendé Gloria | Assistante financière | Administratif et Finance |
| KOLAGBE Saint André | Analyste financier | Equity |
| OUEDRAOGO Samira | Analyste financier | Conseil |
| Job ZONGO | Directeur général | Administratif et Finance |

### 5.2 Départements officiels

Les départements à créer dans le référentiel sont :

| Département | Slug recommandé |
|---|---|
| Administratif et Finance | `administratif-finance` |
| Conseil | `conseil` |
| Equity | `equity` |
| Accélération | `acceleration` |

### 5.3 Emails professionnels

Chaque collaborateur devra être associé à son email professionnel Microsoft 365. Cette donnée est indispensable pour :

- relier le compte connecté au profil collaborateur ;
- envoyer les notifications ;
- contrôler les droits ;
- éviter la création de demandes pour un mauvais profil.

Si les emails ne sont pas disponibles au moment du développement, prévoir un champ nullable et une page d’administration permettant de les compléter.

## 6. Rôles et autorisations

### 6.1 Rôles applicatifs existants

Le modèle `User` contient déjà les rôles suivants :

```text
admin
finance
direction
manager
user
```

Le module Congés doit réutiliser cette logique. Il peut ajouter des capacités plus fines via des tables dédiées, sans nécessairement créer de nouveaux rôles globaux.

### 6.2 Rôles fonctionnels dans le module Congés

| Rôle fonctionnel | Description | Droits |
|---|---|---|
| Collaborateur | Tout salarié actif | Consulter son solde, créer une demande, consulter son historique |
| Validateur congés | Collaborateur autorisé à traiter les demandes | Voir les demandes en attente, valider, rejeter, commenter |
| Super Admin | Administrateur global du portail | Gérer collaborateurs, validateurs, soldes, paramètres, imports |
| Direction | DG ou personne habilitée | Consultation globale, validation si configurée |
| Admin RH/Finance | Personne chargée du suivi administratif | Import historique, ajustements, consultation globale |

### 6.3 Validateurs initiaux

Les validateurs initiaux sont :

| Collaborateur | Rôle initial dans le module |
|---|---|
| BAKO / NAGALO Germaine | Validateur congés |
| OUEDRAOGO Relwendé Gloria | Validateur congés |
| Job ZONGO | Validateur congés |

Ces validateurs ne doivent pas être codés en dur. Ils doivent être créés via seeder ou interface d’administration, puis rester configurables par le Super Admin.

### 6.4 Règle de configuration des validateurs

Créer une table dédiée permettant de définir les validateurs et leur périmètre.

Périmètres recommandés :

| Scope | Signification |
|---|---|
| `global` | Le validateur voit toutes les demandes |
| `department` | Le validateur voit les demandes d’un département |
| `employee` | Le validateur voit les demandes d’un collaborateur spécifique |

Pour le MVP, les trois validateurs initiaux peuvent être configurés en scope `global`.

## 7. Règles métier Congés

### 7.1 Acquisition des droits

La règle de base est :

```text
2,5 jours de congés acquis par mois
```

Le paramètre doit être stocké en base de données afin d’être modifiable par le Super Admin.

Champ recommandé :

```text
leave_settings.monthly_accrual_days = 2.5
```

### 7.2 Moment d’acquisition

Le moment d’acquisition n’a pas encore été définitivement arbitré. Le système doit donc le rendre configurable.

Valeurs proposées :

| Valeur | Description |
|---|---|
| `end_of_month` | Les 2,5 jours sont acquis à la fin du mois complet |
| `start_of_month` | Les 2,5 jours sont acquis dès le début du mois |
| `prorated` | Les droits sont calculés au prorata du temps écoulé |

Valeur par défaut recommandée pour MVP : `end_of_month`, sauf confirmation contraire de l’équipe Néré.

### 7.3 Décompte des jours consommés

Le décompte se fait uniquement en jours calendaires.

Règle :

```text
Nombre de jours demandés = date de fin - date de début + 1
```

Conséquences :

| Cas | Nombre de jours consommés |
|---|---:|
| Du 01/08/2026 au 01/08/2026 | 1 |
| Du 01/08/2026 au 05/08/2026 | 5 |
| Du vendredi au lundi | 4 |
| Samedi et dimanche uniquement | 2 |

Les samedis, dimanches et jours fériés sont comptés. Aucune exclusion de jours ouvrés ne doit être appliquée dans le calcul.

### 7.4 Solde disponible

Le solde disponible doit être calculé ainsi :

```text
Solde disponible = solde initial importé + droits acquis depuis la date de référence - congés approuvés - ajustements
```

Le système doit aussi afficher :

| Indicateur | Définition |
|---|---|
| Solde disponible | Solde réellement disponible hors demandes en attente |
| Jours en attente | Total des jours de demandes soumises mais non encore validées |
| Solde projeté | Solde disponible moins jours en attente |

### 7.5 Demande dépassant le solde

Par défaut, une demande ne peut pas être approuvée si le nombre de jours demandés dépasse le solde disponible.

Comportement recommandé :

| Moment | Règle |
|---|---|
| À la soumission | Alerter le demandeur si la demande dépasse son solde projeté |
| À la validation | Bloquer l’approbation si le solde est insuffisant, sauf override Super Admin |
| En cas d’override | Enregistrer une justification obligatoire dans l’audit log |

### 7.6 Chevauchement de demandes

Un collaborateur ne doit pas pouvoir avoir deux demandes qui se chevauchent lorsque l’une des demandes est en statut :

```text
submitted
under_review
approved
```

Si une demande rejetée ou annulée existe sur la même période, elle ne doit pas bloquer une nouvelle demande.

### 7.7 Périodes sensibles

Certaines périodes peuvent être administrativement sensibles. Le module doit prévoir une table `leave_blackout_periods`.

Niveaux proposés :

| Niveau | Effet |
|---|---|
| `info` | Affiche une alerte au demandeur et au validateur |
| `warning` | Alerte renforcée, commentaire validateur obligatoire |
| `blocked` | La demande est bloquée, sauf Super Admin |

Pour le MVP, la création de périodes sensibles peut être livrée avec une interface simple en administration, ou laissée prête au niveau base de données si le temps manque.

## 8. Workflow fonctionnel

### 8.1 Statuts d’une demande

Statuts recommandés :

| Statut | Description |
|---|---|
| `draft` | Demande préparée mais non soumise |
| `submitted` | Demande soumise par le collaborateur |
| `under_review` | Demande ouverte ou prise en charge par un validateur |
| `approved` | Demande validée |
| `rejected` | Demande rejetée |
| `cancelled` | Demande annulée |

Pour le MVP, `draft` peut être omis si la soumission est directe. Mais la base doit pouvoir l’accueillir plus tard.

### 8.2 Parcours collaborateur

1. Le collaborateur se connecte via Microsoft 365.
2. Il ouvre `/conges`.
3. Il consulte son solde.
4. Il clique sur “Nouvelle demande”.
5. Il renseigne la période de congé.
6. Il voit le nombre de jours calendaires calculé automatiquement.
7. Il ajoute un commentaire facultatif.
8. Il soumet la demande.
9. Les validateurs actifs sont notifiés.
10. Il peut suivre le statut dans son historique.
11. Il reçoit une notification de validation ou de rejet.

### 8.3 Parcours validateur

1. Le validateur reçoit un email Outlook avec un lien direct vers la demande.
2. Il ouvre `/conges/validations/{id}`.
3. Il voit le collaborateur, la période, le nombre de jours, le solde disponible, les jours en attente et les alertes éventuelles.
4. Il peut approuver ou rejeter.
5. Il doit ajouter un commentaire en cas de rejet.
6. Après décision, le demandeur est notifié.
7. Si la demande est approuvée, un PDF est généré et archivé.

### 8.4 Validation simple

Pour le MVP, une seule décision de validation suffit. Si Germaine, Gloria ou Job valide, la demande passe en statut `approved`. Si l’un des validateurs rejette, la demande passe en statut `rejected`.

Une évolution future pourra introduire une validation multi-niveaux.

## 9. Notifications Office 365

### 9.1 Principe

Les notifications doivent utiliser l’architecture Microsoft 365, idéalement via Microsoft Graph. L’objectif est d’éviter un SMTP externe et de rester dans l’environnement de travail existant.

### 9.2 Notifications minimales

| Déclencheur | Destinataire | Objet recommandé |
|---|---|---|
| Demande soumise | Validateurs actifs | Nouvelle demande de congé à valider |
| Demande approuvée | Demandeur | Votre demande de congé a été approuvée |
| Demande rejetée | Demandeur | Votre demande de congé a été rejetée |
| Erreur d’archivage SharePoint | Super Admin | Erreur d’archivage d’une demande de congé |

### 9.3 Contenu de l’email aux validateurs

L’email doit contenir :

- nom du demandeur ;
- département ;
- période demandée ;
- nombre de jours calendaires ;
- solde disponible ;
- commentaire du demandeur ;
- lien direct vers la demande.

Lien type :

```text
https://tools.nerecapital.com/conges/validations/{leave_request_id}
```

### 9.4 Contenu de l’email au demandeur

L’email doit contenir :

- statut final ;
- période demandée ;
- nombre de jours ;
- validateur ayant pris la décision ;
- commentaire éventuel ;
- lien vers le détail de la demande.

Lien type :

```text
https://tools.nerecapital.com/conges/{leave_request_id}
```

### 9.5 Implémentation technique

Créer un service :

```text
app/Services/Microsoft/GraphMailService.php
```

Ou, si un service Microsoft générique existe déjà :

```text
app/Services/Microsoft/MicrosoftGraphService.php
```

Le service doit :

- récupérer un token applicatif ou délégué selon l’architecture retenue ;
- envoyer des emails via Graph ;
- journaliser le succès ou l’échec ;
- ne jamais exposer les tokens dans les logs.

Prévoir un fallback configurable vers Laravel Mail uniquement pour développement local.

## 10. Archivage SharePoint

### 10.1 Principe

Après approbation d’une demande, l’application doit générer un PDF puis le stocker dans un dossier SharePoint privé dédié aux congés.

### 10.2 Structure SharePoint recommandée

Structure par collaborateur :

```text
SharePoint / RH / Congés / 2026 / DIAMITANI Josias Mansour / Demande_conge_2026-08-01_2026-08-15.pdf
```

Structure alternative par mois :

```text
SharePoint / RH / Congés / 2026 / 08 - Août / DIAMITANI Josias Mansour - 2026-08-01 au 2026-08-15.pdf
```

Recommandation : structure par collaborateur, plus lisible pour l’historique individuel.

### 10.3 Fallback local sécurisé

Si SharePoint n’est pas configuré ou si l’upload échoue, le PDF doit être stocké dans :

```text
storage/app/private/leaves/
```

Il ne doit jamais être placé directement dans `/public`.

Le téléchargement doit passer par un contrôleur Laravel qui vérifie les droits.

### 10.4 Métadonnées à stocker

La table `leave_documents` doit contenir :

| Champ | Description |
|---|---|
| `leave_request_id` | Demande liée |
| `file_name` | Nom du PDF |
| `local_path` | Chemin local sécurisé, si fallback |
| `sharepoint_drive_id` | Drive SharePoint cible |
| `sharepoint_item_id` | Identifiant du fichier SharePoint |
| `sharepoint_web_url` | Lien web SharePoint |
| `status` | `generated`, `uploaded`, `failed` |
| `error_message` | Erreur éventuelle |

## 11. Document PDF à générer

### 11.1 Moment de génération

Le PDF est généré automatiquement après approbation de la demande.

### 11.2 Contenu du PDF

Le PDF doit contenir :

| Élément | Description |
|---|---|
| Titre | Demande de congé |
| Collaborateur | Nom complet |
| Poste | Poste officiel |
| Département | Département |
| Date de soumission | Date et heure |
| Période demandée | Date début et date fin |
| Nombre de jours | Jours calendaires |
| Solde avant demande | Solde au moment de la décision |
| Solde après demande | Solde après déduction |
| Décision | Approuvée ou rejetée |
| Validateur | Nom du validateur |
| Date de décision | Date et heure |
| Commentaire demandeur | Si présent |
| Commentaire validateur | Si présent |
| Référence interne | Identifiant unique de la demande |

### 11.3 Nom de fichier

Convention recommandée :

```text
Demande_conge_{YYYYMMDD}_{YYYYMMDD}_{NomNormalise}.pdf
```

Exemple :

```text
Demande_conge_20260801_20260815_DIAMITANI_Josias_Mansour.pdf
```

### 11.4 Rendu visuel

Le PDF doit respecter le style institutionnel Néré :

- page A4 portrait ;
- logo Néré si disponible ;
- titre sobre ;
- tableau clair des informations ;
- couleurs sobres : brun, ocre, gris ;
- pas d’ornement superflu ;
- dates au format `jj/mm/aaaa` ;
- mise en page lisible même imprimée.

Moteur recommandé pour MVP : DomPDF, sauf contrainte de rendu.

## 12. Modèle de données recommandé

### 12.1 Table `departments`

```text
id
name
slug
is_active
created_at
updated_at
```

### 12.2 Évolution de la table `employees`

La table `employees` existe déjà. Ne pas la dupliquer. Ajouter les champs nécessaires par migration.

Champs à ajouter :

```text
email nullable unique
department_id nullable foreign key
hire_date nullable date
leave_eligible boolean default true
leave_reference_date nullable date
```

Notes :

- `display_name` doit rester le nom affiché dans les interfaces et PDFs.
- `job_title` existe déjà et doit être réutilisé.
- Les données Timesheets existantes ne doivent pas être supprimées.

### 12.3 Table `leave_settings`

```text
id
key unique
value
value_type
created_at
updated_at
```

Seed initial :

| Key | Value | Type |
|---|---|---|
| `monthly_accrual_days` | `2.5` | decimal |
| `day_counting_method` | `calendar_days` | string |
| `accrual_policy` | `end_of_month` | string |
| `allow_negative_balance_override` | `false` | boolean |

### 12.4 Table `leave_types`

```text
id
name
slug
is_paid
counts_against_balance
requires_attachment
is_active
created_at
updated_at
```

Seed MVP :

| Name | Slug | Counts against balance |
|---|---|---|
| Congé annuel | `annual_leave` | Oui |

### 12.5 Table `leave_balances`

Cette table stocke les soldes de référence et les soldes agrégés.

```text
id
employee_id foreign key
reference_date date
initial_acquired_days decimal(8,2)
initial_taken_days decimal(8,2)
initial_remaining_days decimal(8,2)
source string nullable
notes text nullable
created_at
updated_at
```

Le fichier Excel historique doit alimenter cette table.

### 12.6 Table `leave_balance_adjustments`

Cette table permet les corrections manuelles par un admin.

```text
id
employee_id foreign key
adjustment_days decimal(8,2)
reason text
created_by_user_id foreign key
created_at
updated_at
```

Une correction positive augmente le solde. Une correction négative le réduit.

### 12.7 Table `leave_requests`

```text
id
uuid unique
employee_id foreign key
leave_type_id foreign key
start_date date
end_date date
requested_days decimal(8,2)
status string
requester_comment text nullable
submitted_at datetime nullable
reviewed_at datetime nullable
reviewed_by_user_id nullable foreign key
reviewer_comment text nullable
balance_before decimal(8,2) nullable
balance_after decimal(8,2) nullable
created_by_user_id foreign key
cancelled_at datetime nullable
cancelled_by_user_id nullable foreign key
created_at
updated_at
```

Statuts possibles :

```text
draft
submitted
under_review
approved
rejected
cancelled
```

### 12.8 Table `leave_approvals`

Cette table garde une trace décisionnelle, même si le MVP n’a qu’un validateur.

```text
id
leave_request_id foreign key
validator_user_id foreign key
action string
comment text nullable
created_at
updated_at
```

Actions possibles :

```text
opened
approved
rejected
commented
cancelled
```

### 12.9 Table `leave_validators`

```text
id
employee_id foreign key
user_id nullable foreign key
scope string default global
department_id nullable foreign key
target_employee_id nullable foreign key
can_approve boolean default true
can_reject boolean default true
notify_by_email boolean default true
is_active boolean default true
created_by_user_id nullable foreign key
created_at
updated_at
```

### 12.10 Table `leave_blackout_periods`

```text
id
name
start_date date
end_date date
scope string default global
department_id nullable foreign key
severity string default info
message text nullable
is_active boolean default true
created_by_user_id foreign key
created_at
updated_at
```

### 12.11 Table `leave_documents`

```text
id
leave_request_id foreign key
file_name
local_path nullable
sharepoint_drive_id nullable
sharepoint_item_id nullable
sharepoint_web_url nullable
status string default generated
error_message text nullable
generated_at datetime nullable
uploaded_at datetime nullable
created_at
updated_at
```

### 12.12 Table `leave_imports`

```text
id
file_name
reference_date date
imported_by_user_id foreign key
status string
row_count integer default 0
success_count integer default 0
error_count integer default 0
error_report_path nullable
created_at
updated_at
```

### 12.13 Table `leave_import_rows`

```text
id
leave_import_id foreign key
employee_display_name
matched_employee_id nullable foreign key
raw_payload json
status string
error_message text nullable
created_at
updated_at
```

## 13. Routes à créer

Toutes les routes doivent être protégées par `auth`. Les routes d’administration doivent être protégées par rôle ou capacité.

```php
Route::middleware('auth')->group(function (): void {
    Route::get('/conges', [LeaveDashboardController::class, 'index'])->name('leaves.index');
    Route::get('/conges/demande', [LeaveRequestController::class, 'create'])->name('leaves.create');
    Route::post('/conges/demande', [LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::get('/conges/historique', [LeaveHistoryController::class, 'index'])->name('leaves.history');
    Route::get('/conges/{leaveRequest:uuid}', [LeaveRequestController::class, 'show'])->name('leaves.show');
    Route::post('/conges/{leaveRequest:uuid}/cancel', [LeaveRequestController::class, 'cancel'])->name('leaves.cancel');

    Route::middleware('can:validate-leaves')->group(function (): void {
        Route::get('/conges/validations/en-attente', [LeaveValidationController::class, 'index'])->name('leaves.validations.index');
        Route::get('/conges/validations/{leaveRequest:uuid}', [LeaveValidationController::class, 'show'])->name('leaves.validations.show');
        Route::post('/conges/validations/{leaveRequest:uuid}/approve', [LeaveValidationController::class, 'approve'])->name('leaves.validations.approve');
        Route::post('/conges/validations/{leaveRequest:uuid}/reject', [LeaveValidationController::class, 'reject'])->name('leaves.validations.reject');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin/conges', [LeaveAdminController::class, 'index'])->name('admin.leaves.index');
        Route::get('/admin/conges/validateurs', [LeaveValidatorController::class, 'index'])->name('admin.leaves.validators.index');
        Route::post('/admin/conges/validateurs', [LeaveValidatorController::class, 'store'])->name('admin.leaves.validators.store');
        Route::put('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'update'])->name('admin.leaves.validators.update');
        Route::delete('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'destroy'])->name('admin.leaves.validators.destroy');

        Route::get('/admin/conges/soldes', [LeaveBalanceController::class, 'index'])->name('admin.leaves.balances.index');
        Route::post('/admin/conges/soldes/adjust', [LeaveBalanceController::class, 'adjust'])->name('admin.leaves.balances.adjust');

        Route::get('/admin/conges/import', [LeaveImportController::class, 'create'])->name('admin.leaves.import.create');
        Route::post('/admin/conges/import', [LeaveImportController::class, 'store'])->name('admin.leaves.import.store');

        Route::get('/admin/conges/parametres', [LeaveSettingsController::class, 'edit'])->name('admin.leaves.settings.edit');
        Route::put('/admin/conges/parametres', [LeaveSettingsController::class, 'update'])->name('admin.leaves.settings.update');
    });
});
```

## 14. Contrôleurs à créer

```text
app/Http/Controllers/Leaves/LeaveDashboardController.php
app/Http/Controllers/Leaves/LeaveRequestController.php
app/Http/Controllers/Leaves/LeaveHistoryController.php
app/Http/Controllers/Leaves/LeaveValidationController.php
app/Http/Controllers/Leaves/Admin/LeaveAdminController.php
app/Http/Controllers/Leaves/Admin/LeaveValidatorController.php
app/Http/Controllers/Leaves/Admin/LeaveBalanceController.php
app/Http/Controllers/Leaves/Admin/LeaveImportController.php
app/Http/Controllers/Leaves/Admin/LeaveSettingsController.php
```

Règles :

- Les contrôleurs doivent rester minces.
- La logique de calcul doit être dans des services.
- Les transitions de statut doivent passer par un service de workflow.
- Les contrôleurs ne doivent pas appeler directement Microsoft Graph, sauf via un service dédié.

## 15. Services à créer

```text
app/Services/Leaves/LeaveDayCountService.php
app/Services/Leaves/LeaveAccrualService.php
app/Services/Leaves/LeaveBalanceService.php
app/Services/Leaves/LeaveRequestWorkflowService.php
app/Services/Leaves/LeaveValidatorService.php
app/Services/Leaves/LeavePdfService.php
app/Services/Leaves/LeaveSharePointService.php
app/Services/Leaves/LeaveImportService.php
app/Services/Leaves/LeaveNotificationService.php
app/Services/Microsoft/GraphMailService.php
app/Services/Microsoft/SharePointStorageService.php
```

### 15.1 `LeaveDayCountService`

Responsabilité : calculer le nombre de jours calendaires.

Règle centrale :

```php
$days = $startDate->diffInDays($endDate) + 1;
```

Ne pas exclure les week-ends. Ne pas exclure les jours fériés.

### 15.2 `LeaveAccrualService`

Responsabilité : calculer les droits acquis depuis une date de référence.

Paramètres :

- date d’embauche ;
- date de référence historique ;
- date cible ;
- politique d’acquisition ;
- nombre de jours acquis par mois.

### 15.3 `LeaveBalanceService`

Responsabilité : calculer les soldes.

Sorties attendues :

```text
initial_remaining_days
accrued_since_reference
approved_days_since_reference
adjustments
available_balance
pending_days
projected_balance
```

### 15.4 `LeaveRequestWorkflowService`

Responsabilité :

- créer une demande ;
- soumettre une demande ;
- approuver ;
- rejeter ;
- annuler ;
- bloquer les transitions invalides ;
- écrire les logs décisionnels.

### 15.5 `LeaveNotificationService`

Responsabilité :

- préparer les messages ;
- déterminer les destinataires ;
- appeler `GraphMailService` ;
- journaliser l’envoi.

### 15.6 `LeavePdfService`

Responsabilité : générer le PDF de demande validée.

### 15.7 `LeaveSharePointService`

Responsabilité :

- déterminer le chemin SharePoint ;
- uploader le PDF ;
- récupérer l’URL SharePoint ;
- mettre à jour `leave_documents`.

### 15.8 `LeaveImportService`

Responsabilité :

- importer l’ancien fichier Excel ;
- matcher les noms avec les collaborateurs actifs ;
- créer ou mettre à jour les soldes initiaux ;
- produire un rapport d’erreur.

## 16. Interfaces utilisateur à créer

Les vues doivent utiliser le layout existant et les classes `nc-*` du design system.

### 16.1 Carte sur le dashboard

Ajouter dans le seeder `tools` :

| Champ | Valeur |
|---|---|
| name | Demandes de congé |
| slug | `leaves` ou `conges` |
| description | Soumettre, suivre et valider les demandes de congé, avec calcul des soldes et notifications Office 365. |
| route | `/conges` |
| status | `active` |
| required_role | `user` ou `null` selon logique du dashboard |
| display_order | `20` |

### 16.2 Page `/conges`

Objectif : tableau de bord personnel.

Contenu :

- titre “Demandes de congé” ;
- carte solde disponible ;
- carte jours en attente ;
- carte solde projeté ;
- bouton “Nouvelle demande” ;
- tableau des dernières demandes personnelles ;
- encart explicatif : “Le décompte est effectué en jours calendaires.”

### 16.3 Page `/conges/demande`

Objectif : formulaire de nouvelle demande.

Champs :

- type de congé ;
- date de début ;
- date de fin ;
- nombre de jours calculé en direct côté UI si possible ;
- commentaire facultatif ;
- résumé du solde avant soumission ;
- bouton “Soumettre la demande”.

Messages :

- si date fin avant date début : afficher erreur ;
- si solde insuffisant : afficher alerte ;
- si période sensible : afficher alerte ;
- si chevauchement avec une demande existante : bloquer.

### 16.4 Page détail `/conges/{uuid}`

Objectif : consulter une demande.

Contenu :

- statut ;
- période ;
- jours demandés ;
- solde ;
- commentaires ;
- timeline des événements ;
- lien PDF si disponible ;
- lien SharePoint si disponible.

### 16.5 Page `/conges/validations/en-attente`

Objectif : file d’attente des validateurs.

Colonnes :

- demandeur ;
- département ;
- période ;
- jours ;
- solde disponible ;
- statut ;
- date de soumission ;
- action.

### 16.6 Page `/conges/validations/{uuid}`

Objectif : revue et décision.

Contenu :

- fiche collaborateur ;
- solde disponible ;
- solde projeté ;
- alertes ;
- commentaire demandeur ;
- formulaire de décision ;
- bouton “Approuver” ;
- bouton “Rejeter” ;
- commentaire obligatoire en cas de rejet.

### 16.7 Page `/conges/historique`

Objectif : historique personnel.

Filtres :

- année ;
- statut ;
- type de congé.

Tableau :

- date de demande ;
- période ;
- jours ;
- statut ;
- décision ;
- document.

### 16.8 Administration `/admin/conges`

Objectif : cockpit du module Congés.

Cartes :

- Soldes collaborateurs ;
- Validateurs ;
- Import historique ;
- Paramètres ;
- Périodes sensibles ;
- Documents SharePoint.

### 16.9 Administration des validateurs

Objectif : rendre les validateurs personnalisables par Super Admin.

Fonctions :

- lister les validateurs actifs ;
- ajouter un validateur ;
- choisir son périmètre ;
- activer ou désactiver les notifications ;
- désactiver un validateur ;
- afficher les derniers changements.

## 17. Import de l’ancien fichier Excel

### 17.1 Objectif

Importer la situation historique des congés comme point de départ.

L’import ne doit pas remplacer la liste RH récente des collaborateurs. Il doit seulement alimenter les soldes initiaux.

### 17.2 Date de référence

Le fichier fourni correspond à une situation au 31/07/2025. La date de référence par défaut doit être :

```text
2025-07-31
```

Cette date doit être modifiable lors de l’import si nécessaire.

### 17.3 Matching collaborateurs

Le matching doit être prudent.

Ordre recommandé :

1. email si disponible ;
2. `employee_id` si disponible ;
3. nom affiché exact normalisé ;
4. nom/prénom normalisés ;
5. validation manuelle si ambiguïté.

Ne jamais créer automatiquement un collaborateur actif inconnu à partir de l’Excel historique sans confirmation admin.

### 17.4 Données importées

Champs à récupérer si disponibles :

| Donnée Excel | Destination |
|---|---|
| Nom collaborateur | Matching employee |
| Date d’embauche | `employees.hire_date` si vide |
| Total acquis historique | `leave_balances.initial_acquired_days` |
| Total pris historique | `leave_balances.initial_taken_days` |
| Solde restant | `leave_balances.initial_remaining_days` |
| Notes ou commentaires | `leave_balances.notes` |

### 17.5 Rapport d’import

Après import, afficher :

- nombre de lignes lues ;
- nombre de collaborateurs matchés ;
- nombre d’erreurs ;
- lignes ambiguës ;
- lignes ignorées ;
- bouton pour télécharger un rapport CSV d’erreurs.

## 18. Sécurité et confidentialité

### 18.1 Données sensibles

Le module manipulera des données RH :

- soldes de congés ;
- demandes individuelles ;
- validations ou rejets ;
- commentaires administratifs ;
- documents PDF ;
- historique d’absence.

Ces données ne doivent jamais être exposées publiquement.

### 18.2 Règles de sécurité

- Toutes les routes Congés doivent être protégées par `auth`.
- Les utilisateurs doivent être autorisés localement.
- Un collaborateur ne voit que ses propres demandes.
- Un validateur ne voit que les demandes relevant de son périmètre.
- Un Super Admin voit tout.
- Les PDF ne doivent pas être accessibles par URL publique directe.
- Les téléchargements passent par un contrôleur qui vérifie les droits.
- Les tokens Microsoft Graph ne doivent jamais être loggés.
- Les erreurs de production ne doivent pas révéler de détails techniques.

### 18.3 Audit

Chaque action sensible doit être journalisée :

| Action | À journaliser |
|---|---|
| Création demande | utilisateur, période, jours |
| Soumission | utilisateur, horodatage |
| Ouverture par validateur | validateur, demande |
| Approbation | validateur, solde avant, solde après |
| Rejet | validateur, commentaire |
| Annulation | utilisateur, raison |
| Ajustement de solde | admin, valeur, justification |
| Changement de validateur | admin, ancien/nouveau |
| Import Excel | fichier, lignes, succès, erreurs |

Une table `audit_logs` générique peut être créée si elle n’existe pas déjà.

## 19. Configuration Microsoft 365

### 19.1 Variables d’environnement

L’authentification Microsoft existe déjà. Pour le module Congés, prévoir en plus :

```env
MS_GRAPH_TENANT_ID=
MS_GRAPH_CLIENT_ID=
MS_GRAPH_CLIENT_SECRET=
GRAPH_MAIL_SENDER_USER_ID=
SHAREPOINT_LEAVES_SITE_ID=
SHAREPOINT_LEAVES_DRIVE_ID=
SHAREPOINT_LEAVES_ROOT_FOLDER_ID=
LEAVES_SHAREPOINT_ENABLED=false
LEAVES_GRAPH_MAIL_ENABLED=false
```

### 19.2 Permissions Graph à prévoir

Permissions minimales possibles selon l’architecture :

```text
User.Read
Mail.Send
Files.ReadWrite
Sites.Selected
```

Le principe de moindre privilège doit être appliqué. L’application ne doit accéder qu’au site et au dossier SharePoint nécessaires.

### 19.3 Mode développement

En local, si Microsoft Graph n’est pas configuré :

- les notifications peuvent être loggées dans `storage/logs/laravel.log` ;
- les PDF peuvent être stockés localement ;
- l’interface doit indiquer que SharePoint et Outlook ne sont pas actifs.

## 20. Seeders à prévoir

### 20.1 Départements

Créer un seeder `DepartmentSeeder`.

### 20.2 Collaborateurs

Mettre à jour les collaborateurs existants ou en créer de nouveaux selon la liste source de vérité.

Attention : la table `employees` contient déjà certains collaborateurs pour le module Timesheets. Il faut faire des `updateOrCreate` sur un identifiant stable, idéalement email si disponible, sinon `display_name`.

### 20.3 Outil Congés

Ajouter dans `DatabaseSeeder` ou un `ToolSeeder` :

```php
Tool::query()->updateOrCreate(
    ['slug' => 'conges'],
    [
        'name' => 'Demandes de congé',
        'description' => 'Soumettre, suivre et valider les demandes de congé, avec calcul des soldes et notifications Office 365.',
        'route' => '/conges',
        'status' => Tool::STATUS_ACTIVE,
        'required_role' => User::ROLE_USER,
        'display_order' => 20,
    ],
);
```

### 20.4 Paramètres congés

Seed initial :

```text
monthly_accrual_days = 2.5
day_counting_method = calendar_days
accrual_policy = end_of_month
```

### 20.5 Validateurs initiaux

Créer Germaine, Gloria et Job comme validateurs globaux actifs si leurs fiches employé existent.

## 21. Tests à écrire

### 21.1 Tests unitaires

| Test | Attendu |
|---|---|
| `LeaveDayCountServiceTest::same_day_counts_as_one` | 1 jour |
| `LeaveDayCountServiceTest::friday_to_monday_counts_four_days` | 4 jours |
| `LeaveDayCountServiceTest::weekends_are_counted` | Week-ends inclus |
| `LeaveAccrualServiceTest::monthly_accrual_is_two_point_five` | 2,5 jours par mois |
| `LeaveBalanceServiceTest::calculates_available_pending_projected_balance` | Soldes corrects |
| `LeaveRequestWorkflowTest::employee_can_submit_request` | Statut `submitted` |
| `LeaveRequestWorkflowTest::validator_can_approve_request` | Statut `approved` |
| `LeaveRequestWorkflowTest::validator_can_reject_request_with_comment` | Statut `rejected` |
| `LeaveRequestWorkflowTest::reject_requires_comment` | Validation bloquante |
| `LeaveRequestWorkflowTest::overlapping_request_is_blocked` | Erreur claire |

### 21.2 Tests fonctionnels

| Test | Attendu |
|---|---|
| Utilisateur non connecté | Redirection login |
| Collaborateur standard | Accès à `/conges`, pas à `/conges/validations` |
| Validateur | Accès à la file de validation |
| Admin | Accès à `/admin/conges` |
| Soumission | Notification validateurs dispatchée ou loggée |
| Approbation | PDF créé et document enregistré |
| Rejet | Notification demandeur dispatchée ou loggée |
| Téléchargement PDF | Autorisé seulement selon droits |

### 21.3 Tests de non-régression

Après ajout du module Congés, les tests existants doivent continuer à passer :

```bash
php vendor/phpunit/phpunit/phpunit
npm run build
```

Le module Timesheets ne doit pas être cassé.

## 22. Critères d’acceptation

### 22.1 Collaborateur

Le module est accepté si un collaborateur autorisé peut :

1. se connecter avec son compte Microsoft 365 ;
2. ouvrir `/conges` ;
3. voir son solde disponible ;
4. créer une demande de congé ;
5. voir le nombre de jours calendaires calculé automatiquement ;
6. soumettre la demande ;
7. voir le statut “en attente” ;
8. recevoir un email après décision ;
9. consulter l’historique de ses demandes.

### 22.2 Validateur

Le module est accepté si un validateur peut :

1. recevoir une notification après soumission ;
2. ouvrir le lien de validation ;
3. consulter la demande et les soldes ;
4. approuver ou rejeter avec commentaire ;
5. déclencher la notification au demandeur ;
6. déclencher la génération PDF en cas d’approbation.

### 22.3 Super Admin

Le module est accepté si un Super Admin peut :

1. gérer les validateurs ;
2. importer l’historique Excel ;
3. consulter et ajuster les soldes ;
4. configurer les paramètres de congés ;
5. consulter les documents et logs ;
6. activer ou désactiver les intégrations Graph selon la configuration.

### 22.4 Office 365 et SharePoint

Le module est accepté si :

- les notifications peuvent être envoyées via l’architecture Microsoft 365 en production ;
- les documents peuvent être archivés dans SharePoint si la configuration est active ;
- un fallback local sécurisé fonctionne si SharePoint est désactivé ;
- les liens profonds renvoient vers les bonnes pages après authentification.

## 23. Roadmap recommandée

### Phase 1 : socle Congés local

- Créer migrations, modèles, seeders.
- Ajouter carte dashboard.
- Créer pages `/conges`, `/conges/demande`, `/conges/historique`.
- Créer services de calcul et workflow.
- Ajouter tests unitaires.

### Phase 2 : validation

- Ajouter validateurs configurables.
- Créer file de validation.
- Ajouter approbation et rejet.
- Ajouter notifications en mode log/local.
- Ajouter tests fonctionnels.

### Phase 3 : import historique

- Créer import Excel.
- Ajouter matching prudent.
- Ajouter rapport d’import.
- Alimenter les soldes initiaux.

### Phase 4 : PDF et archivage

- Générer PDF après approbation.
- Stocker localement dans `storage/app/private/leaves`.
- Ajouter téléchargement sécurisé.
- Ajouter intégration SharePoint si configuration disponible.

### Phase 5 : notifications Microsoft Graph

- Brancher envoi Outlook via Graph.
- Ajouter logs d’envoi.
- Ajouter erreurs lisibles en admin.

### Phase 6 : durcissement

- Vérifier permissions.
- Ajouter audit logs complets.
- Optimiser UI.
- Tester données réelles.
- Préparer mise en production.

## 24. Risques et mitigations

| Risque | Impact | Mitigation |
|---|---|---|
| Mauvais matching Excel | Soldes incorrects | Matching prudent et validation manuelle des ambiguïtés |
| Emails absents | Notifications impossibles | Champ email obligatoire avant activation production |
| Graph mal configuré | Pas de notifications ou SharePoint | Fallback local, messages admin clairs |
| Droits trop larges | Exposition RH | Policies, scopes validateurs, tests d’autorisation |
| PDF exposés publiquement | Risque confidentialité | Stockage privé et téléchargement via contrôleur |
| Règle d’acquisition ambiguë | Solde contesté | Paramètre configurable et validation métier par Néré |
| Trop de complexité MVP | Retard | Validation simple et périmètre maîtrisé |

## 25. Instructions spécifiques à un agent AI développeur

L’agent AI chargé de développer ce module doit respecter ces consignes :

1. Ne pas créer une nouvelle application Laravel.
2. Ajouter le module au projet existant.
3. Ne pas casser `/timesheets`.
4. Réutiliser l’authentification Microsoft existante.
5. Réutiliser le design system existant.
6. Ne pas hardcoder les validateurs.
7. Créer des migrations additives, jamais destructrices sans demande explicite.
8. Écrire les calculs métier dans des services testables.
9. Ajouter les tests avant ou pendant l’implémentation.
10. Ne jamais stocker de PDF RH dans `/public`.
11. Ne jamais committer de secrets Microsoft 365.
12. Ajouter des erreurs utilisateur lisibles en français.
13. Préserver la logique de droits actuelle.
14. Documenter toute nouvelle variable `.env` dans `.env.example`.
15. Ajouter le module Congés dans le catalogue `tools`.

## 26. Livrables attendus

À la fin du développement MVP, les livrables attendus sont :

- migrations Congés ;
- modèles Eloquent ;
- seeders collaborateurs, départements, paramètres, outil Congés et validateurs initiaux ;
- contrôleurs Congés ;
- services métier ;
- vues Blade ;
- styles réutilisant le design system ;
- notifications ;
- génération PDF ;
- stockage local sécurisé ;
- intégration SharePoint si activée ;
- import Excel historique ;
- tests unitaires et fonctionnels ;
- mise à jour `.env.example` ;
- mise à jour `README.md` ;
- mise à jour éventuelle de `design.md` si nouveaux composants réutilisables.

## 27. Critère de succès final

Le module Congés sera considéré comme réussi si un collaborateur Néré peut se connecter à `tools.nerecapital.com` avec son compte Microsoft 365, ouvrir le module “Demandes de congé”, consulter son solde, soumettre une demande calculée en jours calendaires, déclencher une notification aux validateurs configurés, recevoir une décision par email, et retrouver son document de congé généré et archivé dans un espace SharePoint privé ou, à défaut, dans un stockage local sécurisé.

Le tout doit s’intégrer proprement au portail Laravel existant, sans altérer le module Feuilles de temps, sans exposer de données RH publiquement et sans introduire de dette technique inutile.
