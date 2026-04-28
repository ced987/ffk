# Demo Sprint 5 - Detail des participants cote organisateur

## Objectif

Demontrer que l'organisateur peut consulter en lecture seule les participants inscrits, regroupes par club.

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
12. Verifier la section `Participants inscrits par club`.
13. Verifier que les participants sont regroupes par club.
14. Verifier les informations affichees :
    - nom ;
    - prenom ;
    - sexe ;
    - age ;
    - poids approximatif ;
    - numero de licence si present.
15. Verifier qu'il n'y a aucune action de modification, suppression ou validation.
16. Selectionner `Utilisateur Club B`.
17. Verifier que Club B voit uniquement ses propres participants.
18. Selectionner `Utilisateur Club C`.
19. Verifier que la competition ne s'affiche pas.

## Regles verifiees

- Seul le club organisateur voit le detail des participants de tous les clubs.
- Chaque club invite voit uniquement ses propres participants.
- L'affichage organisateur est en lecture seule.
- Les participants viennent des `InscriptionOperationnelle` liees a la competition.
- Les compteurs Sprint 4 restent visibles.

## Hors perimetre

- Modification participant.
- Suppression participant.
- Validation participant.
- Alertes de completude.
- Date limite.
- Retrait participant ou club.
- Duplication.
- Import licencies.
- Poules, combats, classement.
