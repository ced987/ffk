# Démo Sprint 7 - Retrait d'un participant

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir ou créer une compétition organisée par Club A.
3. Ajouter un participant pour Club A.
4. Vérifier que le bouton Retirer apparaît dans Participants de mon club.
5. Retirer le participant Club A.
6. Vérifier qu'il n'apparaît plus dans les listes actives et qu'il n'est plus compté.
7. Inviter Club B, marquer l'invitation envoyée, puis passer en Utilisateur Club B.
8. Confirmer la participation de Club B.
9. Ajouter un participant pour Club B.
10. Retirer le participant Club B.
11. Vérifier que Club B ne voit plus ce participant dans sa liste active.
12. Repasser en Utilisateur Club A.
13. Vérifier que les compteurs organisateur ne comptent plus le participant retiré.
14. Vérifier que Club A ne peut toujours pas retirer les participants des clubs invités.
15. Passer en Utilisateur Club C.
16. Vérifier que Club C ne voit pas la compétition.

## Règles métier vérifiées

- Le retrait passe `InscriptionOperationnelle.is_active` à `false`.
- Aucune suppression physique n'est faite.
- Le participant reste en base.
- L'inscription reste rattachée à la compétition et au club.
- Seul le club propriétaire peut retirer son participant.
- Le club organisateur peut retirer uniquement ses propres participants.
- Les participants inactifs ne sont plus affichés dans les listes actives.
- Les participants inactifs ne sont plus comptés dans les compteurs.
- Aucune restauration, validation ou suppression définitive n'est ajoutée.
