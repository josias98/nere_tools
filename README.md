# Générateur de feuilles de temps Néré Capital

Cette application Streamlit génère automatiquement les feuilles de temps mensuelles en PDF, à partir d'une clé de répartition analytique et d'une période sélectionnée.

## Installation locale

```bash
python -m venv .venv
source .venv/bin/activate  # Windows: .venv\Scripts\activate
pip install -r requirements.txt
```

## Configuration des identifiants

L'application est protégée par une authentification simple basée sur les secrets Streamlit. Créez un fichier local `.streamlit/secrets.toml` avec la structure suivante :

```toml
[auth]
username = "admin"
password = "change-me"
```

Remplacez ces valeurs par vos vrais identifiants locaux. Le fichier `.streamlit/secrets.toml` est ignoré par Git et ne doit pas être commité. Un exemple sans secret réel est disponible dans `.streamlit/secrets.example.toml`.

Sur Streamlit Community Cloud, ajoutez la même section `[auth]` dans les secrets de l'application depuis les paramètres de déploiement.

## Logo de l'interface

Pour afficher le logo Néré/I&P dans l'interface Streamlit, placez le fichier ici :

```text
assets/logo.png
```

Si ce fichier est absent, l'application continue de fonctionner et affiche simplement un titre texte. La génération PDF conserve sa logique de logo existante, notamment le logo par défaut `assets/logo_extracted.jpg` lorsqu'il est disponible.

## Lancement de l'application

```bash
streamlit run app.py
```

Sous Windows, si la commande `streamlit` n'est pas reconnue, utilisez plutôt :

```powershell
py -m streamlit run .\app.py
```

Ouvrez ensuite l'URL locale affichée par Streamlit, généralement `http://localhost:8501`, puis connectez-vous avec les identifiants configurés dans `.streamlit/secrets.toml`.

## Déploiement Streamlit Community Cloud

1. Poussez le projet sur GitHub avec `app.py`, `requirements.txt`, `timesheet_generator.py`, `employees_template.csv` et les assets publics nécessaires.
2. Dans Streamlit Community Cloud, créez ou ouvrez l'application reliée au dépôt GitHub.
3. Vérifiez que le fichier principal est `app.py`.
4. Dans les paramètres de l'application, ajoutez les secrets :

```toml
[auth]
username = "admin"
password = "change-me"
```

5. Remplacez les valeurs d'exemple par les identifiants réels avant de partager l'application.
6. Redéployez l'application si nécessaire.

## Logique métier intégrée

- Une page PDF est générée par salarié et par mois couvert par la période.
- Le découpage hebdomadaire suit la logique observée dans les feuilles existantes : première ligne du 1er jour du mois jusqu'au premier dimanche, puis lundi-dimanche, puis dernière semaine partielle si nécessaire.
- La date de signature correspond au premier jour ouvré suivant la fin du mois.
- Par défaut, un jour ouvré est un jour du lundi au vendredi.
- Les jours fériés doivent être saisis dans l'application si vous voulez les exclure des dates de signature.
- La colonne IPAS est fixée à 0% par défaut. Si une colonne IPAS existe dans la clé importée, elle sera utilisée.
- Pour les profils DAF/AAF et DG, la feuille utilise la structure spéciale : CATAL1,5°T, IPDE, Autre projets, Commentaires/Détails.
- La colonne Autre projets est calculée automatiquement comme le complément permettant d'obtenir 100% avec CATAL1,5°T et IPDE. Si une colonne Autre projets existe dans le fichier importé, sa valeur est utilisée.
- Pour le DG, le bloc de cosignature affiche Signature du DAF et peut utiliser Germaine comme cosignataire. Pour Germaine/DAF-AAF, le DG est utilisé comme responsable hiérarchique lorsqu'aucun cosignataire n'est renseigné.

## Format attendu de la clé

L'application accepte un fichier CSV ou Excel contenant au minimum :

- Prénom
- Nom
- Entité
- Pays
- Fonction
- Rôle
- IPDE
- CATAL1.5°

Colonnes optionnelles utiles :

- IPAS
- Fonds
- Autre projets
- Code analytique
- Lieu
- Nom signature
- Responsable hiérarchique
- Titre signature droite

## Utilisation en ligne de commande

```bash
python timesheet_generator.py \
  --input employees_template.csv \
  --start 2026-01-01 \
  --end 2026-06-30 \
  --output-dir output \
  --logo assets/logo_extracted.jpg \
  --employees "Alida OUEDRAOGO" "Josias Mansour DIAMITANI" \
  --holidays 2026-05-01
```

Un fichier PDF par salarié sera généré, ainsi qu'un ZIP contenant tous les PDFs.

## Version 1.3

- Ajout de la colonne Autre projets pour Germaine/DAF-AAF et Job/DG.
- Calcul automatique du complément pour atteindre 100%.
- Maintien du cas particulier de cosignature du DG par le DAF.
