# Workflows

## Branches

- `website`: branche principale du site WordPress.
- `website/<sujet>`: branche courte pour une modification non triviale.

Chaque changement doit finir par un commit clair. Pour une petite modification, travailler directement sur `website` reste acceptable.

## Developpement Local

1. Garder le dump SQL local a la racine: `9f1r7v_myd_infomaniak_com.sql`.
2. Lancer ou reinitialiser l'environnement:

```powershell
.\scripts\dev.ps1 -Reset
```

3. Ouvrir `http://localhost:8080`.
4. Modifier en priorite:
   - `wp-content/themes/financity-child/` pour le theme.
   - `wp-content/plugins/nere-*` pour les plugins custom.
5. Tester au minimum les fichiers PHP touches:

```powershell
php -l chemin\du\fichier.php
```

## Plugins Custom

Regle simple: le code metier custom va dans un plugin `nere-*`, pas dans le theme parent.

Utiliser le theme enfant uniquement pour:

- templates WordPress;
- CSS du theme;
- hooks strictement lies au rendu.

Utiliser un plugin custom pour:

- shortcodes;
- taxonomies;
- endpoints AJAX;
- logique metier reutilisable;
- integrations externes.

## Deploiement Infomaniak

Le deploiement est manuel:

1. Faire un commit local.
2. Exporter une archive propre depuis Git:

```powershell
git archive --format=zip --output=nere-website.zip website
```

3. Televerser l'archive sur l'hebergement Infomaniak.
4. Decompresser dans le dossier web.
5. Garder le `wp-config.php` de production en place.
6. Ne pas remplacer `wp-content/uploads/` sauf si tu televerses volontairement les medias.
7. Si une base importee contient encore des URLs locales, lancer le script de remplacement:

```bash
php scripts/db-url-replace.php --from=http://localhost:8080 --to=https://nerecapital.com
```

Le script est CLI-only et conserve les donnees serialisees WordPress.
