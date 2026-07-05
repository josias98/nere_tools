# NERE Capital Website

Site WordPress de NERE Capital (`nerecapital.com`) base sur le theme GoodLayers Financity.

## Developpement Local

Prerequis: Docker Desktop.

Le dump SQL local doit rester a la racine avec ce nom:

```powershell
9f1r7v_myd_infomaniak_com.sql
```

Premier lancement ou reimport complet:

```powershell
.\scripts\dev.ps1 -Reset
```

Lancements suivants:

```powershell
.\scripts\dev.ps1
```

Le site sera disponible sur:

```text
http://localhost:8080
```

La base locale est exposee sur `localhost:3307` avec `wordpress / wordpress`.

## Organisation

- `wp-content/themes/financity-child/`: personnalisations du site.
- `wp-content/themes/financity/`: theme parent GoodLayers.
- `wp-content/plugins/`: plugins WordPress installes.
- `local/wp-config.local.php`: configuration locale montee dans Docker.
- `compose.yaml`: WordPress + MariaDB pour developpement local.
- `docs/workflows.md`: workflow de developpement et de deploiement.
- `scripts/db-url-replace.php`: remplacement d'URL en base apres import.

Les secrets, dumps SQL, caches, sauvegardes et uploads ne sont pas versionnes. Les uploads ont ete retires volontairement pour reduire la taille; les images seront donc manquantes en local tant que `wp-content/uploads/` n'est pas restaure.

## Workflows

Voir [docs/workflows.md](docs/workflows.md) pour le workflow de developpement, les conventions de plugins custom et le deploiement manuel vers Infomaniak.
