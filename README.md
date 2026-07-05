# NERE Capital Website

Site WordPress de NERE Capital (`nerecapital.com`) basé sur le thème GoodLayers Financity.

## Développement Local

Prérequis: Docker Desktop.

Le dump SQL local doit rester à la racine avec ce nom:

```powershell
9f1r7v_myd_infomaniak_com.sql
```

Premier lancement ou réimport complet:

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

La base locale est exposée sur `localhost:3307` avec `wordpress / wordpress`.

## Organisation

- `wp-content/themes/financity-child/`: personnalisations du site.
- `wp-content/themes/financity/`: thème parent GoodLayers.
- `wp-content/plugins/`: plugins WordPress installés.
- `local/wp-config.local.php`: configuration locale montée dans Docker.
- `compose.yaml`: WordPress + MariaDB pour développement local.

Les secrets, dumps SQL, caches, sauvegardes et uploads ne sont pas versionnés. Les uploads ont été retirés volontairement pour réduire la taille; les images seront donc manquantes en local tant que `wp-content/uploads/` n'est pas restauré.
