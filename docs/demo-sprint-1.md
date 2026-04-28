# Demo Sprint 1 - Competitions interclubs

## Objectif

Demontrer le parcours Sprint 1 :

Club A cree une competition, ajoute Club B en pre-invite, marque l'invitation comme envoyee, Club B voit la competition, Club C ne la voit pas.

## Prerequis

- PHP et Composer installes.
- Dependances installees avec `composer install`.
- Base locale SQLite disponible dans `database/database.sqlite`.

## Reset de demo

Depuis la racine du projet :

```bash
php artisan migrate:fresh --seed
```

Ce reset cree :

- Club A, Club B, Club C ;
- un utilisateur par club ;
- une competition initiale organisee par Club A ;
- une invitation initiale de Club B au statut `pre_invite`.

## Lancer l'application

```bash
php artisan serve
```

Ouvrir ensuite l'URL affichee par Laravel, par exemple :

```text
http://127.0.0.1:8000
```

Si le port 8000 est deja utilise :

```bash
php artisan serve --port=8001
```

## Scenario manuel

1. Ouvrir la page d'accueil.
2. Selectionner `Utilisateur Club A`.
3. Creer une competition avec un nom de demo, par exemple `Demo Sprint 1`.
4. Ouvrir `Mes competitions`.
5. Ouvrir le detail de `Demo Sprint 1`.
6. Dans `Ajouter un club pre-invite`, choisir `Club B`.
7. Valider l'ajout.
8. Verifier dans `Clubs invites` que `Club B` apparait avec le statut `pre_invite`.
9. Revenir a l'accueil et selectionner `Utilisateur Club B`.
10. Ouvrir `Mes competitions`.
11. Verifier que `Demo Sprint 1` n'apparait pas encore.
12. Revenir a l'accueil et selectionner `Utilisateur Club A`.
13. Ouvrir le detail de `Demo Sprint 1`.
14. Dans `Clubs invites`, cliquer sur `Marquer envoyee` pour Club B.
15. Verifier que le statut de Club B passe a `invite`.
16. Revenir a l'accueil et selectionner `Utilisateur Club B`.
17. Ouvrir `Mes competitions`.
18. Verifier que `Demo Sprint 1` apparait avec `Organisateur : Club A`.
19. Ouvrir le detail de la competition.
20. Revenir a l'accueil et selectionner `Utilisateur Club C`.
21. Ouvrir `Mes competitions`.
22. Verifier que `Demo Sprint 1` n'apparait pas.

## Regle metier verifiee

Une competition est visible si le club courant est organisateur ou si le club courant a une invitation au statut `invite`.

Une invitation au statut `pre_invite` ne rend pas la competition visible au club invite.

L'action T09 `Marquer envoyee` fait passer l'invitation de `pre_invite` a `invite`.

## Tests

Pour verifier le socle Sprint 1 :

```bash
php artisan test
```

Le resultat attendu est une suite de tests verte.
