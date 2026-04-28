# Démo Sprint 10 - Préparation des poules

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Vérifier la section Poules.
4. Créer une poule, par exemple `Poule Benjamin A`.
5. Vérifier que la poule apparaît avec le statut `brouillon`.
6. Vérifier la section Participants éligibles aux poules.
7. Vérifier que seuls les participants actifs et validés de la compétition apparaissent.
8. Vérifier que les participants non validés ou retirés ne sont pas dans cette liste.
9. Passer en Utilisateur Club B.
10. Ouvrir la compétition et vérifier que Club B ne voit pas le formulaire de création de poule.
11. Passer en Utilisateur Club C.
12. Vérifier que Club C ne voit pas la compétition.

## Règles vérifiées

- Seul l'organisateur crée une poule.
- Une poule appartient à une compétition.
- Une poule démarre au statut `brouillon`.
- Le vivier éligible est global à la compétition.
- Le vivier éligible contient uniquement les inscriptions `is_active = true` et `is_validated = true`.
- Aucune affectation à une poule n'est créée.
- Aucun modèle d'affectation, combat, classement ou verrouillage n'est ajouté.
