# Démo Sprint 14 - Génération simple des combats

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir `Competition Demo Sprint 10`.
3. Créer une poule.
4. Affecter au moins deux participants actifs et validés.
5. Figer la poule.
6. Cliquer `Générer les combats`.
7. Vérifier que la liste des combats apparaît sous la poule.
8. Vérifier le format `A vs B`.
9. Vérifier que le statut de chaque combat est `a_saisir`.
10. Vérifier que le bouton de génération disparaît après génération.

## Règles vérifiées

- La poule doit être `figee`.
- La génération est réservée à l'organisateur.
- Les combats sont générés en tous contre tous, une fois par paire.
- L'ordre est déterministe selon les ids croissants des inscriptions.
- Les inscriptions utilisées sont uniquement actives, validées et dans la poule.
- Aucune régénération n'est possible si des combats existent déjà.
- Aucun score, tour, classement, suppression ou régénération n'est ajouté.
