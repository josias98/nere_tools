# Wizard demande de conge

## Decisions essentielles

- Parcours fixe en 5 etapes : Type, Periode, Informations, Verification, Confirmation. Les champs restent conditionnels, mais la structure ne bouge pas.
- Le conge annuel est mis en avant comme choix probable quand il existe, sans selection automatique.
- Les calculs visibles pendant la periode viennent du serveur : duree, reprise, jours non comptes, solde estime, chevauchements et restrictions.
- Le resume lateral reste visible sur desktop. Sur mobile, il devient repliable et les actions restent en bas d'ecran.
- Le brouillon serveur garde les champs du parcours. Les pieces jointes restent dans le flux temporaire prive Livewire jusqu'a la soumission. Aucune donnee RH n'est stockee dans `localStorage` ou `sessionStorage`.
- La page de verification exige une confirmation explicite avant soumission definitive.
- La confirmation finale est un vrai ecran avec reference, statut, prochaine etape et liens de suite, pas un simple toast.

## Faits observes dans le code

- Le workflow existant cree deja la chaine Superviseur -> RH -> DG, les snapshots de regles, l'audit, les notifications et les documents.
- Les services existants savent calculer les soldes, les jours ouvres et la reprise effective.
- Timesheets fournit un precedent utile pour le stepper, le stage Livewire, les actions persistantes et les micro-interactions.
- L'ancien formulaire congés calculait la duree et le solde en JavaScript navigateur.

## Hypotheses UX a tester

- Les salaries comprennent mieux le choix du type sous forme de cartes qu'un select.
- Les RH preferent voir les avertissements de solde comme alertes non bloquantes tant que le workflow peut arbitrer.
- Les demandeurs ont besoin d'un remplaçant surtout pour les absences longues, pas pour toutes les demandes.
- Le resume lateral suffit a eviter les retours inutiles avant l'etape Verification.
