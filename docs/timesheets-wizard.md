# Assistant Feuilles de temps

## Décision PWA

La PWA n'est pas activée. Le workflow dépend de Microsoft 365, manipule des données RH et ne peut ni valider ni générer hors ligne. Un cache limité au shell offrirait peu de bénéfice tout en ajoutant un cycle de mise à jour et un risque de cache de réponses authentifiées. La décision pourra être revue si un besoin d'installation mobile mesuré apparaît ; toute future stratégie devra exclure les réponses authentifiées, CSV, allocations, PDF et ZIP.

## Architecture

`Timesheets\\Wizard` orchestre six vues d'étape. Les imports CSV et la saisie manuelle convergent vers le même tableau de lignes, validé côté Livewire puis transmis à `TimesheetService`. Les composants Blade `components/ui` fournissent les primitives réutilisables. Le contrôleur historique reste compatible avec les anciens POST.

Le brouillon contient des données métier : il est conservé uniquement dans le `sessionStorage` de l'onglet, jamais dans un cache PWA, et disparaît à la fermeture de l'onglet. Toute valeur restaurée reste non fiable et repasse par la validation serveur. Les fichiers temporaires restent gérés par Livewire dans le stockage temporaire privé et suivent sa politique de nettoyage.

## Déploiement

Build à exécuter en CI ou sur la machine de préparation :

```sh
npm ci
npm run build
```

Commandes serveur, après livraison de `public/build` :

```sh
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

Déployer le contenu compilé de `public/build` permet de ne pas installer Node.js sur le serveur de production. Le worker de queue n'est pas requis par cette version : la génération affiche honnêtement un état bloquant synchrone.
