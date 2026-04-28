# Démo Sprint 11 - Affectation simple aux poules

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Créer une poule au statut `brouillon`.
4. Dans Participants disponibles pour affectation, choisir une poule et cliquer Affecter.
5. Vérifier que le participant apparaît sous la poule.
6. Vérifier que ce participant ne reste plus dans les disponibles.
7. Vérifier que seuls les participants actifs, validés et non affectés sont disponibles.
8. Retirer un participant déjà affecté.
9. Vérifier que son affectation est automatiquement supprimée.
10. Passer Club B et vérifier qu'il ne peut pas affecter de participant.
11. Passer Club C et vérifier que la compétition n'est pas visible.

## Règles vérifiées

- Une inscription porte au plus une `poule_id`.
- Seul l'organisateur affecte un participant.
- La poule et l'inscription doivent appartenir explicitement à la même compétition.
- L'affectation est possible uniquement vers une poule `brouillon`.
- Les disponibles sont strictement `is_active = true`, `is_validated = true`, `poule_id = null`.
- La désactivation d'une inscription remet automatiquement `poule_id` à `null`.
- Aucun modèle `AffectationPoule` n'est créé.
- Aucun déplacement, retrait manuel d'affectation, combat, classement ou figement n'est ajouté.
