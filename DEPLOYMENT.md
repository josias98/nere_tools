# Déploiement en production

Le serveur web doit pointer vers `public/`. Construire les assets et l'archive sur une machine disposant de Node.js ; aucun appel à Node.js ou npm n'est requis sur le serveur.

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
LEAVE_CERTIFICATE_SIGNATORY_NAME="Monsieur ZONGO P. Job"
LEAVE_CERTIFICATE_SIGNATORY_TITLE="Directeur Général"
```

Configurer également la base de données, Microsoft 365, Microsoft Graph Mail, la queue et une clé `APP_KEY` persistante. Vérifier que `storage/` et `bootstrap/cache/` sont accessibles en écriture par PHP.

Pour Sentry, définir `SENTRY_LARAVEL_DSN` uniquement dans le `.env` serveur. Conserver `SENTRY_SEND_DEFAULT_PII=false` et régler `SENTRY_TRACES_SAMPLE_RATE` selon le volume attendu. La configuration PHP doit contenir `zend.exception_ignore_args = Off` et un magasin de certificats valide via `curl.cainfo` (ou le magasin CA du système).

## Après chaque livraison

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Sur un hébergement mutualisé, planifier régulièrement :

```bash
php artisan queue:work --stop-when-empty
```

Ne jamais exécuter `php artisan key:generate` sur une application déjà en production : changer la clé rendrait illisibles les données chiffrées et invaliderait les sessions.

## Contrôles rapides

- Ouvrir `/up`, puis tester la connexion Microsoft 365.
- Vérifier qu'une URL inconnue affiche la page Néré 404 sans détail technique.
- Tester une demande de congé jusqu'au PDF, son QR/lien public et l'envoi d'email.
- Tester un import de soldes et une génération/export de feuilles de temps.
- Confirmer que `public/build/manifest.json` est présent et qu'aucun fichier `.env` ou log n'est publiquement accessible.
