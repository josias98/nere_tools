# Déploiement en production

Le serveur web doit pointer vers `public/`. Construire les assets et l'archive sur une machine disposant de Node.js ; aucun appel à Node.js ou npm n'est requis sur le serveur.

Pour le nouvel outil Signature PDF, appliquer les migrations et suivre la
[configuration du certificat](docs/pdf-signatures.md). Le mode est désactivé par
défaut ; les certificats de développement sont interdits en production. Conserver
le PKCS#12 et son secret hors de l’archive de livraison.

## Avant l'envoi

- Exécuter `npm ci && npm run build` localement et vérifier que `public/build/manifest.json` existe.
- Installer les dépendances PHP de production avec `composer install --no-dev --optimize-autoloader` avant de créer l'archive si `vendor/` doit y être inclus.
- Ne pas inclure `.env`, les fichiers `*.log`, les bases locales, `storage/logs/*`, les caches locaux, les tests, `node_modules/` ou les fichiers temporaires.
- Conserver le fichier `.env` de production uniquement sur le serveur ; il ne doit jamais être commité ni livré dans l'archive.

## Configuration serveur

Valeurs minimales :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tools.nerecapital.com
LOG_LEVEL=error
QUEUE_CONNECTION=deferred
LEAVE_CERTIFICATE_SIGNATORY_NAME="Monsieur ZONGO P. Job"
LEAVE_CERTIFICATE_SIGNATORY_TITLE="Directeur Général"
LEAVE_APPROVAL_REMINDER_HOURS=48
```

Configurer également la base de données, Microsoft 365, Microsoft Graph Mail, la queue et une clé `APP_KEY` persistante. Vérifier que `storage/` et `bootstrap/cache/` sont accessibles en écriture par PHP.

La queue `deferred` reprend le fonctionnement du serveur : les jobs sont exécutés
après l'envoi de la réponse HTTP, dans le même processus PHP. Aucun worker
permanent ni cron de consommation de queue n'est nécessaire pour les emails.
Les échecs restent visibles dans les notifications de l'administration et peuvent
y être relancés manuellement ; `deferred` ne conserve pas les jobs en base et
n'applique pas les reprises automatiques d'un worker `database`.

Pour les relances automatiques des validations, ajouter ces identifiants dédiés
au WebCron dans le `.env` avant de mettre la configuration en cache :

```env
WEBCRON_USERNAME=nere-tools-cron
WEBCRON_PASSWORD=<mot-de-passe-aleatoire-long>
```

Pour Sentry, définir `SENTRY_LARAVEL_DSN` uniquement dans le `.env` serveur. Conserver `SENTRY_SEND_DEFAULT_PII=false` et régler `SENTRY_TRACES_SAMPLE_RATE` selon le volume attendu. La configuration PHP doit contenir `zend.exception_ignore_args = Off` et un magasin de certificats valide via `curl.cainfo` (ou le magasin CA du système).

## Après chaque livraison

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Le planificateur Infomaniak appelle une URL plutôt qu'une commande shell.
Créer la tâche WebCron avec :

- nom : `Nere Tools - relances validations` ;
- tâche activée ;
- protocole : `https` ;
- URL : `tools.nerecapital.com` ;
- chemin du script : `webcron/queue` ;
- URL protégée : oui, avec `WEBCRON_USERNAME` et `WEBCRON_PASSWORD` ;
- fréquence : toutes les 15 minutes ;
- notifications en cas d'échec : activées.

Chaque appel recherche les validations arrivées au délai de relance. Avec
`deferred`, leurs emails sont envoyés à la fin de cette requête ; aucun worker
n'est démarré. Après une modification de `.env`, relancer
`php artisan config:cache` pour que le serveur charge les nouvelles valeurs.

Si un environnement conserve explicitement `QUEUE_CONNECTION=database`, le même
WebCron traite en plus un job par appel, ou un worker `php artisan queue:work`
peut être lancé séparément. Ne pas lancer de worker avec la queue `deferred`.

Ne jamais exécuter `php artisan key:generate` sur une application déjà en production : changer la clé rendrait illisibles les données chiffrées et invaliderait les sessions.

## Contrôles rapides

- Ouvrir `/up`, puis tester la connexion Microsoft 365.
- Vérifier qu'une URL inconnue affiche la page Néré 404 sans détail technique.
- Tester une demande de congé jusqu'au PDF, son QR/lien public et l'envoi d'email.
- Tester un import de soldes et une génération/export de feuilles de temps.
- Confirmer que `public/build/manifest.json` est présent et qu'aucun fichier `.env` ou log n'est publiquement accessible.
