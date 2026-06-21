# Design system - Nere Tools

Statut: specification vivante. Ce document fixe le langage visuel actuel de
Nere Tools; il pourra evoluer apres validation des ecrans et des exports PDF.

## Intention

Nere Tools est un outil de travail interne. L'interface doit etre sobre,
rapide a scanner, accessible, et orientee execution. Elle ne doit pas adopter
un langage marketing: pas de page hero, pas de grandes illustrations
decoratives, pas de gradients spectaculaires, pas de cartes empilees sans
fonction.

Le ton visuel est institutionnel, precis et calme. Les ecrans doivent aider un
collaborateur a comprendre ou il se trouve, quelles actions sont possibles, et
quels elements demandent son attention.

## Assets de marque

- Logo principal: `public/brand/nere-capital-rgb.png` sur fond clair.
- Logo sombre: `public/brand/nere-capital-black.png` si necessaire sur fond tres clair.
- Logo clair: `public/brand/nere-capital-white.png` uniquement sur fond brun sombre.
- Ne pas deformer, recolorer, compresser visuellement ou ajouter d'ombre au logo.
- Le logo doit rester un repere de marque, pas un element decoratif geant.

## Tokens

Les tokens actifs vivent dans `resources/css/app.css`. Ce fichier reste la
source de verite tant que le systeme reste compact.

Couleurs:

- Brun principal: `#532609`
- Brun sombre: `#341604`
- Rouille: `#8f342a`
- Ocre: `#a98c4e`
- Gris texte secondaire: `#5f5249`
- Gris clair: `#cdc6c0`
- Orange accent: `#e1580a`
- Blanc: `#ffffff`
- Ivoire fond: `#f7f4f1`
- Surface panneau: `#eeeae7`
- Ligne: `#ded7d1`

Typographies:

- Titres: `"Le Monde Livre", Cambria, Georgia, serif`
- Interface et texte courant: `"Work Sans", Calibri, Arial, sans-serif`
- Chiffres et petits labels techniques: meme famille interface, avec poids
  legerement renforce si necessaire.

Forme:

- Rayon standard: `6px`
- Ombres tres discretes uniquement pour detacher une surface fonctionnelle.
- Bordures visibles et calmes, preferer `#ded7d1` aux effets de profondeur.

## Macrostructure

Le modele d'ecran principal est un workbench:

- En-tete compact avec logo, navigation et session utilisateur.
- Contenu dans `.nc-page`, largeur maximale maitrisee.
- Titre de page avec `.nc-title-row`, marque par une regle verticale brune.
- Actions principales visibles en haut de page ou dans le panneau concerne.
- Navigation par liens explicites, jamais par elements purement decoratifs.

Les pages d'authentification sont plus concentrees:

- Carte unique ou panneau unique.
- Logo visible.
- Message court.
- Bouton Microsoft 365 principal.
- Etats d'erreur lisibles et non techniques.

## Composants

Les classes `nc-*` sont le vocabulaire UI actuel. Ajouter une nouvelle classe
uniquement si elle decrit une intention reutilisable.

- `.nc-panel`: surface de travail principale.
- `.nc-card`: element repete ou module individuel.
- `.nc-section-title`: titre compact dans un panneau.
- `.nc-button`: action principale.
- `.nc-button-secondary`: action secondaire.
- `.nc-alert`: message d'etat ou d'erreur.
- `.nc-table`: donnees tabulaires, lignes lisibles, entetes sobres.
- `.nc-input`, `.nc-select`: controles natifs styles, focus visible.

Ne pas mettre des cartes dans des cartes sauf pour une modale ou un cas de
donnees repetees clairement distinctes.

## Accessibilite

- Contraste suffisant sur tous les textes et controles.
- Focus clavier visible sur liens, boutons, champs et selects.
- Cibles interactives d'au moins 42px de hauteur quand possible.
- Libelles visibles pour les champs importants; placeholders seulement en aide.
- Les messages d'erreur doivent dire ce qui bloque et ce que l'utilisateur peut faire.
- Les tableaux doivent rester lisibles sur mobile via scroll horizontal plutot
  qu'un empilement fragile.

## Responsive

Le breakpoint mobile principal est autour de `760px`.

Sur mobile:

- Le header peut passer en colonnes.
- Les actions peuvent prendre toute la largeur.
- Les grilles passent en une colonne.
- Les textes restent de taille stable; ne pas scaler la typo au viewport.

## Pages attendues

- Authentification Microsoft 365.
- Acces refuse / compte non autorise.
- Dashboard interne.
- Administration.
- Timesheets: generation, resultats, historique, telechargements.
- Pages futures des outils metier, construites sur le meme workbench.

## Exports PDF

Les PDF timesheets ont leur propre layout imprime. Le web design ne doit pas
forcer le rendu PDF. Apres inspection du modele joint, ajouter ici une section
`PDF Timesheets` avec les marges, tailles, en-tetes, tableaux et signatures a
respecter.

## Regle ponytail

Garder le systeme simple. Ne pas extraire de composants ou fichiers
supplementaires tant qu'un motif n'est pas repete au moins trois fois ou qu'il
ne cree pas une vraie complexite de maintenance.
