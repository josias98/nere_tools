# Générateur de feuilles de temps Néré Capital

Cette application génère automatiquement les feuilles de temps mensuelles en PDF, à partir d'une clé de répartition analytique et d'une période sélectionnée.

## Installation

```bash
python -m venv .venv
source .venv/bin/activate  # Windows: .venv\Scripts\activate
pip install -r requirements.txt
```

## Lancement de l'application

```bash
streamlit run app.py
```

Sous Windows, si la commande `streamlit` n'est pas reconnue, utilisez plutôt :

```powershell
py -m streamlit run .\app.py
```

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
