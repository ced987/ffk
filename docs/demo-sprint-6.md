# Démo Sprint 6 - Modification de ses propres participants

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
4. Vérifier que Club A peut cliquer sur Modifier dans la section Participants de mon club.
5. Modifier les informations du participant Club A, puis enregistrer.
6. Inviter Club B, marquer l'invitation envoyée, puis passer en Utilisateur Club B.
7. Confirmer la participation de Club B.
8. Ajouter un participant pour Club B.
9. Vérifier que Club B peut cliquer sur Modifier uniquement sur son participant.
10. Modifier les informations du participant Club B, puis enregistrer.
11. Repasser en Utilisateur Club A.
12. Vérifier que Club A voit toujours les participants par club en lecture seule.
13. Vérifier que Club A ne peut pas modifier le participant de Club B.
14. Passer en Utilisateur Club C.
15. Vérifier que Club C ne voit pas la compétition.

## Règles métier vérifiées

- Un club peut modifier uniquement ses propres participants.
- Un club invité doit être au statut participation_confirmee pour modifier ses participants.
- Le club organisateur peut modifier uniquement ses propres participants.
- L'organisateur ne modifie pas les participants des clubs invités dans Sprint 6.
- L'inscription reste rattachée à la même compétition et au même club.
- Le ParticipantSource reste rattaché au même club.
- Aucune suppression, désactivation ou validation organisateur n'est ajoutée.
