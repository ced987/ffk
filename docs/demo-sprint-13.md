# Démo Sprint 13 - Figer une poule

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Créer une poule brouillon.
4. Affecter au moins deux participants disponibles à cette poule.
5. Cliquer `Figer la poule`.
6. Vérifier que le statut passe à `figee`.
7. Vérifier que les actions `Retirer de la poule` et `Déplacer` ne sont plus disponibles sur cette poule.
8. Vérifier qu'il n'est plus possible d'ajouter un participant à cette poule.
9. Vérifier qu'un participant de cette poule ne peut plus être retiré.
10. Passer Club B et vérifier qu'il ne peut pas figer une poule.
11. Passer Club C et vérifier que la compétition n'est pas visible.

## Règles vérifiées

- Seul l'organisateur peut figer une poule.
- Une poule doit être au statut `brouillon`.
- Une poule doit contenir au moins deux participants actifs et validés.
- Une poule figée passe au statut `figee`.
- Une poule figée ne reçoit plus de participants.
- Les affectations d'une poule figée ne peuvent plus être retirées ou déplacées.
- Un participant dans une poule figée ne peut pas être retiré.
- Aucun défigement, combat, classement, historique ou drag & drop n'est ajouté.
