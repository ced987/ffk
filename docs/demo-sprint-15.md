# Démo Sprint 15 - Saisie simple des résultats

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Créer une poule, affecter au moins deux participants, figer la poule.
4. Générer les combats.
5. Cliquer `Saisir le score` sur un combat.
6. Renseigner `score A` et `score B`, puis enregistrer.
7. Vérifier que le combat affiche le score et le statut `termine`.
8. Cliquer `Modifier le score`.
9. Modifier les scores et vérifier que le combat reste `termine`.
10. Passer Club B et vérifier qu'il ne peut pas saisir de score.

## Règles vérifiées

- Seul l'organisateur saisit ou modifie un score.
- `score_a` et `score_b` sont obligatoires.
- Les scores sont des entiers supérieurs ou égaux à 0.
- L'égalité est autorisée.
- Une saisie complète passe le combat au statut `termine`.
- Un combat terminé reste modifiable.
- Aucun vainqueur, classement, forfait, absence ou historique n'est ajouté.
