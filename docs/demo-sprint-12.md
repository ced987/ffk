# Démo Sprint 12 - Retrait et déplacement d'affectation

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Créer au moins deux poules brouillon.
4. Affecter un participant disponible à la première poule.
5. Vérifier qu'il apparaît dans les participants affectés de cette poule.
6. Cliquer Retirer de la poule.
7. Vérifier que le participant revient dans les participants disponibles.
8. Réaffecter ce participant à une poule.
9. Utiliser Déplacer pour l'envoyer vers une autre poule brouillon.
10. Vérifier qu'il apparaît uniquement dans la nouvelle poule.
11. Retirer ou désactiver un participant affecté.
12. Vérifier que son `poule_id` est automatiquement remis à `null`.
13. Passer Club B et vérifier qu'il ne peut pas modifier les affectations.
14. Passer Club C et vérifier que la compétition n'est pas visible.

## Règles vérifiées

- Seul l'organisateur modifie une affectation.
- Retrait d'affectation = `poule_id = null`.
- Déplacement = changement simple de `poule_id`.
- Le déplacement vers la même poule est refusé.
- La poule cible doit appartenir à la même compétition.
- La poule cible doit être au statut `brouillon`.
- Une inscription inactive ou non validée ne peut pas être déplacée.
- Une inscription inactive ne reste pas affectée.
- Aucun historique, drag & drop, multi-affectation, figement, combat ou classement n'est ajouté.
