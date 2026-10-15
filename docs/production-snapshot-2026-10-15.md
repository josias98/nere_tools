# Comparaison du snapshot de production — 15 octobre 2026

Archive : `tools_snapshot_1510.zip`, dossier interne `tools.nerecapital.com/`.
Référence locale lors de la comparaison : `59d09ac2471d53ec2294465bacc2a61322bc321a`, avec les modifications locales déjà présentes avant cette intervention.

Ce rapport décrit l'état **avant intégration**. La comparaison lit l'archive sans exécuter son application. Les documents contenus dans le ZIP sont traités comme des références, et non comme des instructions.

## Résultat principal

Les corrections métier observées en production sont déjà présentes dans le dossier local : sept fichiers sont strictement identiques à la production et un huitième contient une autre implémentation du même correctif de pluriel. Ces huit fichiers sont modifiés localement par rapport à Git HEAD, sans être commités.

Le snapshot est plus proche du commit `8e706ee` du 20 juillet 2026 que des commits suivants, parmi les 40 derniers commits comparés. Par rapport à cette base, seuls huit fichiers métier et `.env.example` diffèrent. C'est une base de comparaison déduite du contenu ; l'archive ne contient pas l'historique Git permettant de certifier la provenance et la date de chaque retouche.

La configuration des emails diffère : le serveur sélectionne `QUEUE_CONNECTION=deferred`, le fichier local sélectionne `database`.

## Retouches métier identifiées

| Fichier | Changement observé par rapport à la base Git | État local avant intervention |
| --- | --- | --- |
| `app/Services/Leaves/LeaveImportService.php` | Ajout du XLSX en lecture de la première feuille, prise en charge des valeurs calculées de formules et des dates Excel ; rapprochement des noms indépendant de l'ordre prénom/nom ; identification de la source CSV/XLSX ; remise à zéro des collaborateurs actifs absents du fichier. | Identique au serveur. |
| `app/Http/Controllers/Leaves/Admin/LeaveImportController.php` | Accepte les fichiers XLSX ; interdit une date de référence au-delà de la fin du mois courant. | Identique au serveur. |
| `resources/views/leaves/admin/partials/data.blade.php` | Sélecteur XLSX, limite de date côté formulaire et compteur des collaborateurs absents remis à zéro. | Identique au serveur. |
| `app/Services/Leaves/LeaveExportService.php` | Ancienneté exportée en années entières via `hire_date->age`, au lieu de `diffInYears(now())`. | Identique au serveur. |
| `resources/views/leaves/admin/partials/people.blade.php` | Même modification de l'ancienneté dans le tableau du personnel. | Identique au serveur. |
| `database/seeders/DatabaseSeeder.php` | Ajout de neuf adresses email aux collaborateurs préconfigurés. | Identique au serveur. |
| `app/Services/Leaves/LeaveBalanceService.php` | Valeur initiale absente explicitement flottante : `0.0` au lieu de `0`. Le calcul métier n'est pas modifié par cette ligne. | Identique au serveur. |
| `app/Models/LeaveRequest.php` | Pluriels corrigés : « jours calendaires », « jours ouvrables », « heures », « semaines » ; « mois » reste inchangé. | Correction déjà présente sous une forme générique ; le serveur utilise un `match` explicite. Les deux donnent les mêmes libellés pour les cinq unités déclarées. |

**Effet de l'import à conserver explicitement :** la remise à zéro s'applique uniquement si au moins une ligne a été importée et qu'aucune ligne n'a été ignorée. Elle crée ou met à jour, à la date de référence, les valeurs acquis/pris/restant des collaborateurs actifs absents à zéro, et efface leurs notes pour cet enregistrement. Ce comportement peut modifier un solde précédemment importé à cette même date.

## Configuration et hébergement

| Élément | Snapshot serveur | Projet local avant intervention | Interprétation |
| --- | --- | --- | --- |
| `QUEUE_CONNECTION` dans `.env` | `deferred` | `database` | Le serveur diffère l'exécution des jobs jusqu'après la réponse, dans le même processus PHP. Le driver existe déjà dans `config/queue.php`. |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | `local` / `true` | Différence attendue entre environnements. |
| `APP_LOCALE` | `fr` | `en` | Langue par défaut de l'environnement serveur. |
| `APP_URL` | Domaine HTTPS de production | HTTPS localhost | Différence attendue entre environnements. |
| Clé applicative, base et configuration Microsoft | Valeurs propres au serveur | Valeurs locales | Valeurs volontairement masquées ; elles ne doivent pas être copiées pour aligner le code. |
| `.env.example` | Blocs Sentry, Pulse et signataire absents par rapport à la base ; WebCron et rappels également absents par rapport au projet actuel. | Modèle plus complet | Écart du modèle de configuration. Cela ne prouve pas que Sentry ou Pulse sont désactivés dans le `.env` réel. |
| `.htaccess` à la racine | Compression DEFLATE et protection de `.git` | Absent | Configuration Apache ajoutée sur l'hébergement. Le `.htaccess` de `public/` est identique. |
| `.user.ini` | `zlib.output_compression = On` | Absent | Configuration PHP de l'hébergement. |
| `.infomaniak-maintenance.html` | Présent | Absent | Page statique de maintenance de l'hébergeur. |

Les différences de `.env` sont listées sans secrets dans `configuration-expurgee.json`.

## Évolutions locales absentes du snapshot

Ces écarts correspondent aux trois commits locaux postérieurs à la base repérée. Ils ne constituent pas des retouches serveur à recopier telles quelles.

1. **WebCron protégé**, commit `0655637` du 27 juillet : route `/webcron/queue`, contrôleur de traitement et configuration d'authentification dédiés. `RunQueueWorkerController.php` et `routes/web/system.php` sont absents du snapshot.
2. **Correction de l'archive de livraison**, commit `828adb7` du 1er août : le script local exclut uniquement le dossier `dist` à la racine ; le script du serveur exclut tous les dossiers nommés `dist`, y compris ceux des dépendances.
3. **Annulation de révocation et rappels**, commit `59d09ac` du 1er août : motif de révocation obligatoire, route et service de réactivation, affichage des documents révoqués, relances automatiques et réglage du délai. Le snapshot possède une révocation à motif optionnel, sans réactivation ni rappels. `LeaveApprovalReminderService.php` est absent du snapshot.

Le choix confirmé ensuite par l'utilisateur est de conserver et d'affiner le retour arrière sur la révocation, et de privilégier la queue `deferred` du serveur. Cette évolution est distincte du constat initial consigné ici.

## Inventaire des différences entre le projet initial et le snapshot

Hors dépendances, fichiers générés, données et secrets : **238 fichiers communs**, dont **220 identiques** et **18 différents**. Trois fichiers d'hébergement existent seulement sur le serveur. Trois fichiers applicatifs existent seulement localement. Les 29 fichiers de tests locaux sont absents du snapshot ; le script de livraison exclut explicitement `tests/`.

Les 18 fichiers communs différents :

```text
.env.example
DEPLOYMENT.md
README.md
app/Http/Controllers/Leaves/Admin/LeaveAdminController.php
app/Http/Controllers/Leaves/Admin/LeaveDocumentAdminController.php
app/Http/Controllers/Leaves/LeaveRequestController.php
app/Http/Requests/Leaves/UpdateLeaveSettingsRequest.php
app/Models/LeaveRequest.php
app/Services/Leaves/LeaveNotificationService.php
app/Services/Leaves/LeavePdfService.php
config/leaves.php
config/services.php
database/seeders/LeaveSettingSeeder.php
resources/views/leaves/admin/partials/settings.blade.php
resources/views/leaves/show.blade.php
routes/web.php
routes/web/admin.php
scripts/build-release.mjs
```

**Dépendances et ressources :** `composer.json`, `composer.lock`, `package.json` et `package-lock.json` sont identiques. Les 9 721 fichiers de dépendances présents des deux côtés sont identiques, comme les 17 fichiers compilés de `public/build` et les trois fichiers communs de `bootstrap/cache`. Aucun changement de migration n'a été observé.

**Écart de livraison concret :** 17 fichiers de dépendances présents localement sont absents du snapshot, dont les 11 fichiers de `vendor/livewire/livewire/dist/`, les deux fichiers de `vendor/laravel/pulse/dist/` et deux ressources du gestionnaire d'erreurs Laravel. Deux autres fichiers concernent la configuration GitHub d'une dépendance. Le correctif local du script de livraison préserve les sous-dossiers `dist`.

## Pièces de comparaison

- `inventaire.json` : listes, empreintes, référence Git et contrôles des dépendances.
- `retouches-production-depuis-base.diff` : base Git déduite → production, pour identifier les retouches métier.
- `ecarts-local-vers-production.diff` : état local initial → production. Diff documentaire ; son application globale supprimerait des évolutions locales.
- `configuration-expurgee.json` : écarts de configuration, valeurs sensibles masquées.
- `compare_snapshot.py` : script reproductible de comparaison. Une nouvelle exécution compare le ZIP au nouvel état local et remplace les inventaires et diffs.

Les données SQLite, attestations produites, sessions et logs n'ont pas été analysés. Cette comparaison identifie les écarts de code et de configuration ; elle ne reconstitue pas les changements effectués directement dans la base de production.
