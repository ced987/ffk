# Démo Sprint 9 - Synthèse validation participants

## Prérequis

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

## Scénario manuel

1. Sélectionner Utilisateur Club A.
2. Ouvrir ou créer une compétition organisée par Club A.
3. Ajouter deux participants pour Club A.
4. Inviter Club B, marquer l'invitation envoyée, puis passer en Utilisateur Club B.
5. Confirmer la participation de Club B.
6. Ajouter deux participants pour Club B.
7. Repasser en Utilisateur Club A.
8. Valider un participant Club A et un participant Club B.
9. Retirer un participant Club B.
10. Vérifier sur le détail compétition :
    - le compteur global Participants actifs ignore le participant retiré ;
    - le compteur global Participants validés compte uniquement les actifs validés ;
    - le compteur global Participants non validés compte uniquement les actifs non validés ;
    - chaque ligne club affiche actifs, validés et non validés ;
    - le participant retiré reste visible avec Participation annulée, mais n'est pas compté.

## Règles vérifiées

- Aucun nouveau champ n'est ajouté pour Sprint 9.
- Les compteurs sont basés strictement sur `is_active = true`.
- Les participants retirés restent visibles, mais ne comptent ni dans les actifs, ni dans les validés, ni dans les non validés.
- La synthèse ne change pas le workflow de validation.
