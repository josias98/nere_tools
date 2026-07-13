# Centre d’administration

## Architecture

Le centre d’administration organise les actions selon les tâches des utilisateurs : vue d’ensemble, utilisateurs et accès, Congés, Feuilles de temps, workflows, notifications, puis imports et exports. Seules les rubriques reliées à un module existant sont affichées.

Le contrôleur de dashboard orchestre les données de présentation. `ConfigurationDiagnosticService` porte les contrôles de cohérence. Les modifications de paramètres Congés passent par une Form Request puis l’action transactionnelle `UpdateLeaveSettings`; Blade ne contient aucune règle métier.

## Profils et décisions

| Profil | Priorités | Modifications autorisées aujourd’hui |
| --- | --- | --- |
| Administrateur technique | accès, disponibilité, audit | tous les réglages et accès |
| RH / Admin-Finance | soldes, règles, validateurs, notifications | administration existante selon le département |
| Responsable de module | cohérence et exploitation | à valider avant ouverture granulaire |
| Superviseur | demandes et impact équipe | validation métier, pas de réglage global |
| DG | décision finale et risques | validation finale, pas de réglage technique |

Les rôles historiques restent compatibles. L’ouverture de permissions granulaires supplémentaires doit être validée avec les responsables métier avant de modifier le modèle d’autorisation.

## Recommandations et diagnostics

Les recommandations indiquent explicitement leur source : bonne pratique, politique interne ou règle légale. Une bonne pratique n’est jamais présentée comme une obligation légale.

Les diagnostics actuels vérifient les validateurs, les types de congé actifs, les notifications en échec et l’utilisation initiale des feuilles de temps. Chaque anomalie fournit un lien de correction. Les prochains contrôles doivent être ajoutés au service, avec un test de comportement et une route de correction existante.

## Versionnement et audit

Chaque changement de paramètre Congés conserve l’ancienne et la nouvelle valeur, l’auteur, le motif, l’adresse IP, le type et l’effet temporel. Une transaction et un verrou protègent les écritures; un timestamp obsolète bloque une modification concurrente.

## Déploiement

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Le dossier `public/build` produit par Vite doit être inclus dans l’artefact livré. Node.js n’est ainsi pas requis sur le serveur de production.
