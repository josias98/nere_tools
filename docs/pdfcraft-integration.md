# PDFCraft dans Néré Tools

L'intégration utilise l'export statique du projet [PDFCraft](https://github.com/PDFCraftTool/pdfcraft), révision `0be0f8b6a7ada15b60a42d4789238bf49283b494`. La suite d'outils et le traitement PDF restent dans le navigateur. Laravel sert les pages à `/pdfcraft`, applique l'authentification Microsoft et les droits du catalogue d'outils, puis conserve les fichiers sources et résultats dans le disque privé `local`. L'interface publiée est en français et reprend l'identité Néré Tools. Le code et la licence AGPL de PDFCraft restent accessibles dans le paquet publié.

## Construire et publier

Sur le poste de construction, avec Git et Node.js :

```sh
npm run build:pdfcraft
npm run release:zip
```

`build:pdfcraft` récupère la révision figée, applique `integrations/pdfcraft/nere.patch` et `NereArchiveBridge.tsx`, exécute `npm ci` puis construit l'export Next. Les pages HTML sont installées dans `resources/pdfcraft-pages` et les ressources statiques dans `public/pdfcraft`. Ces dossiers sont générés et exclus de Git. La commande `release:zip` exige leur présence et les inclut dans l'archive. Elle conserve aussi `python_stdlib.zip`, utilisé par PyMuPDF. Aucun processus Node.js n'est nécessaire sur le serveur de production.

Sur le serveur : déployer l'archive complète, exécuter `php artisan migrate --force`, puis attribuer l'outil `pdfcraft` aux utilisateurs concernés dans les droits Néré Tools. Le disque `local` doit être inscriptible et hors de la racine publique. L'hébergement doit accepter les requêtes multipartites jusqu'à la limite configurée (`PDFCRAFT_MAX_FILE_KIB=102400` par défaut), avec `upload_max_filesize` et `post_max_size` PHP adaptés. Les fichiers dépassant la limite ne sont pas archivés ni téléchargés par le pont Néré.

L'hébergement Apache utilise `public/.htaccess` pour faire passer `/pdfcraft` et les routes françaises par Laravel, même lorsque les dossiers des payloads Next existent. `public/pdfcraft/.htaccess` ajoute les en-têtes COOP/COEP/CORP et les types MIME WASM/MJS aux ressources statiques. Sur Nginx, reproduire ces en-têtes sur `/pdfcraft/`, faire passer les URL sans extension `/pdfcraft` et `/pdfcraft/fr/...` par `index.php`, et servir directement les fichiers statiques avec extension. Sans isolation croisée, certains outils LibreOffice WASM ne peuvent pas fonctionner.

## Conservation et accès

Le pont navigateur envoie les fichiers sources sélectionnés et les résultats téléchargés à `POST /pdfcraft/api/files`. Chaque entrée contient l'utilisateur, l'opération, l'outil, le type source/résultat, le nom, la taille, le condensat SHA-256 et la date d'expiration. `/pdfcraft/history` affiche les documents de l'utilisateur ; le téléchargement vérifie à nouveau l'identité et l'expiration. Les fichiers expirent après 90 jours par défaut (`PDFCRAFT_RETENTION_DAYS`). Le webcron existant `/webcron/queue` efface les fichiers et lignes expirés par lots de 50 à chaque passage. Il doit continuer d'être appelé régulièrement.

Le traitement PDF reste local au navigateur, mais l'archivage envoie désormais les fichiers à Néré Tools. Les ressources statiques du projet sont publiques et ne contiennent pas les fichiers des utilisateurs ; les pages de l'outil et les API sont protégées par Laravel. La version intégrée ne publie pas les service workers de PDFCraft, afin qu'une page mise en cache ne reste pas disponible après la déconnexion.

## Vérifications et limites

Exécuter `php artisan test --filter=PdfCraftIntegrationTest`. Après une construction, ouvrir `/pdfcraft`, lancer un outil PDF, puis vérifier que la source et le résultat apparaissent dans `/pdfcraft/history` et que le téléchargement d'un autre utilisateur est refusé. Vérifier aussi les en-têtes COOP/COEP dans le navigateur et au moins une conversion LibreOffice.

Le pont intercepte les sélections de fichiers et les téléchargements Blob/data URL de la suite. Une fonctionnalité amont qui utiliserait un autre mécanisme de sortie devra recevoir un adaptateur explicite avant d'être considérée comme archivée. La limite par fichier est de 100 Mio par défaut. L'audit npm de la révision amont signale des dépendances vulnérables ; les avis concernant le serveur Next ne s'appliquent pas directement à l'export statique, mais les bibliothèques exécutées dans le navigateur doivent être réévaluées avant mise en production. La licence AGPL-3.0 de PDFCraft implique également une revue des obligations de diffusion du code source pour l'application déployée.
