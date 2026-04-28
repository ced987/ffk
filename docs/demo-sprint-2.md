# Demo Sprint 2 - Reponse du club invite

## Objectif

Documenter le parcours Sprint 2 :

Club B, invite par Club A, confirme ou refuse sa participation. Club A voit la reponse de Club B. Club C ne voit toujours rien.

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

1. Ouvrir la page d'accueil.
2. Selectionner `Utilisateur Club A`.
3. Ouvrir `Mes competitions`.
4. Ouvrir une competition organisee par Club A.
5. Dans `Ajouter un club pre-invite`, choisir `Club B`.
6. Valider l'ajout.
7. Dans `Clubs invites`, verifier que `Club B` est au statut `pre_invite`.
8. Cliquer sur `Marquer envoyee` pour Club B.
9. Verifier que Club B passe au statut `invite`.
10. Revenir a l'accueil.
11. Selectionner `Utilisateur Club B`.
12. Ouvrir `Mes competitions`.
13. Verifier que la competition est visible avec `Organisateur : Club A`.
14. Ouvrir le detail de la competition.
15. Dans `Reponse de mon club`, confirmer ou refuser la participation.
16. Revenir a l'accueil.
17. Selectionner `Utilisateur Club A`.
18. Ouvrir le detail de la competition.
19. Verifier dans `Recapitulatif des clubs` que le statut de Club B est mis a jour :
    - `Participation confirmee` si Club B a confirme ;
    - `Participation refusee` si Club B a refuse.
20. Revenir a l'accueil.
21. Selectionner `Utilisateur Club C`.
22. Ouvrir `Mes competitions`.
23. Verifier que la competition ne s'affiche pas.

## Regles metier verifiees

- Seul un club au statut `invite` peut repondre.
- Un club au statut `pre_invite` ne peut pas repondre.
- Un club non invite ne voit rien.
- Le club organisateur ne repond pas a sa propre competition.
- Une reponse est definitive dans Sprint 2.
- La confirmation ou le refus ne cree aucune inscription competiteur.

## Tests

Pour verifier le Sprint 2 :

```bash
php artisan test
```

Le resultat attendu est une suite de tests verte.
