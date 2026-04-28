# Démo Sprint 16 - Classement simple par poule

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Créer une poule, affecter au moins trois participants, figer la poule.
4. Générer les combats.
5. Saisir quelques scores.
6. Revenir au détail compétition.
7. Vérifier la section `Classement` sous la poule.
8. Vérifier que tous les participants de la poule apparaissent.
9. Vérifier que seuls les combats `termine` donnent des points.
10. Vérifier les points :
    - victoire : 3 points ;
    - nul : 1 point ;
    - défaite : 0 point.

## Règles vérifiées

- Le classement est calculé à l'affichage.
- Aucun classement n'est stocké en base.
- Tous les participants de la poule apparaissent, même sans combat terminé.
- Les combats non terminés sont ignorés.
- Le tri est points décroissants puis id d'inscription croissant.
- Les égalités de points partagent le même rang.
- Le classement utilise des rangs standards avec trous.
- Aucun départage par score, confrontation directe, forfait ou absence n'est ajouté.
