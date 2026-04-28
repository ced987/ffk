# Demo Sprint 4 - Compteurs d'inscriptions

## Objectif

Demontrer la synthese organisateur des inscriptions sans afficher le detail des participants de tous les clubs.

## Prerequis

Depuis la racine du projet :

```bash
php artisan migrate:fresh --seed
php artisan serve --port=8001
php artisan test
```

Ouvrir ensuite :

```text
http://127.0.0.1:8001
```

## Scenario manuel

1. Selectionner `Utilisateur Club A`.
2. Ouvrir ou creer une competition organisee par Club A.
3. Inscrire un participant pour Club A.
4. Ajouter Club B en pre-invite.
5. Marquer l'invitation de Club B comme envoyee.
6. Selectionner `Utilisateur Club B`.
7. Ouvrir `Mes competitions`, puis le detail de la competition.
8. Confirmer la participation.
9. Inscrire un participant pour Club B.
10. Selectionner `Utilisateur Club A`.
11. Ouvrir le detail de la competition.
12. Verifier dans `Recapitulatif des clubs` :
    - le compteur global de participants inscrits ;
    - le compteur du club organisateur ;
    - le compteur de Club B.
13. Verifier que le detail des participants de Club B n'est pas affiche cote organisateur.
14. Selectionner `Utilisateur Club C`.
15. Verifier que la competition ne s'affiche pas.

## Regles verifiees

- Les compteurs sont calcules depuis `InscriptionOperationnelle`.
- Le club organisateur voit des volumes, pas le detail des participants des clubs invites.
- Un club invite confirme voit uniquement ses propres participants.
- Club C non invite ne voit rien.

## Hors perimetre

- Modification participant.
- Suppression participant.
- Validation participant.
- Alertes de completude.
- Doublons.
- Date limite.
- Retrait.
- Import licencies.
- Poules, combats, classement.
