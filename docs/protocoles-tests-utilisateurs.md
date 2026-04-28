# Protocoles de tests utilisateurs MVP FFK

Objectif : tester rapidement si le MVP est compréhensible, utilisable et cohérent métier, sans explication technique.

## Remise à zéro de la démo

Commande :

```bash
php artisan migrate:fresh --seed
```

À utiliser :
- avant un test utilisateur ;
- après un test utilisateur ;
- dès que les données locales deviennent confuses.

Effet :
- supprime toutes les données locales ;
- recrée les tables ;
- recharge le jeu de données de démonstration.

Données recréées :
- clubs ;
- utilisateurs ;
- compétition de démo ;
- participants ;
- poules ;
- combats ;
- scores.

Comptes disponibles :
- `club-a@example.test` : Club A, organisateur ;
- `club-b@example.test` : Club B, participant confirmé ;
- `club-c@example.test` : Club C, participant confirmé ;
- `club-d@example.test` : Club D, invité non confirmé ;
- `club-e@example.test` : Club E, non concerné.

État attendu après reset :
- compétition `Competition Demo MVP` organisée par Club A ;
- Club B et Club C confirmés ;
- Club D invité mais non confirmé ;
- Club E non invité ;
- participants actifs, validés, non validés et retirés ;
- une poule brouillon ;
- une poule figée ;
- combats générés sur la poule figée ;
- deux scores saisis et un combat non saisi ;
- classement visible sur la poule figée.

## A. Protocole testeur novice

Profil : personne qui ne connaît pas l'application.

Objectif : vérifier si le MVP se comprend sans explication.

Prérequis :
- site lancé en local, par exemple `http://127.0.0.1:8001` ;
- base remise à zéro avec `php artisan migrate:fresh --seed` ;
- commencer avec l'utilisateur Club A si possible.

Consigne donnée au testeur :
> Tu arrives sur l'application. Essaie de comprendre ce que tu peux faire, puis crée une compétition.

Scénario guidé minimal :
1. Ouvrir la page d'accueil.
2. Observer la compétition de démonstration.
3. Ouvrir la compétition de démonstration.
4. Revenir à l'accueil.
5. Créer une nouvelle compétition.
6. Changer d'utilisateur et observer ce qui change.

Ne pas expliquer à l'avance :
- les statuts d'invitation ;
- les règles de visibilité ;
- les poules ;
- les combats ;
- le classement.

Ce que l'observateur doit regarder :
- hésitations avant le premier clic ;
- erreurs de navigation ;
- incompréhensions sur le rôle du club courant ;
- compréhension du badge `Démo` ;
- compréhension de l'action `Créer une compétition` ;
- capacité à revenir à l'accueil ;
- moments où le testeur demande une explication.

Questions après test :
- Qu'as-tu compris de l'application ?
- Quelle était, selon toi, la compétition principale à regarder ?
- Qu'est-ce qui t'a bloqué ?
- Qu'est-ce qui n'était pas clair ?
- Qu'as-tu essayé de faire sans y arriver ?
- Quelle action t'a paru la plus évidente ?
- Quelle action t'a paru cachée ou ambiguë ?

Format de prise de notes :

| Irritant | Impact | Fréquence | Notes |
| --- | --- | --- | --- |
| Exemple : le testeur ne voit pas la création | faible / moyen / fort | 1 fois / plusieurs fois | Contexte exact |

Échelle d'impact :
- faible : gêne mineure, le testeur continue seul ;
- moyen : hésitation ou erreur, mais récupération possible ;
- fort : blocage, besoin d'aide ou abandon.

## B. Protocole testeur avancé

Profil : personne connaissant le projet ou référent produit.

Objectif : tester la cohérence métier de bout en bout.

Prérequis :
- site lancé en local, par exemple `http://127.0.0.1:8001` ;
- base remise à zéro avec `php artisan migrate:fresh --seed` ;
- commencer avec Club A.

### Scénario complet

1. Utiliser la compétition de démonstration existante
   - vérifier que `Competition Demo MVP` apparaît en premier ;
   - vérifier le badge `Démo` ;
   - ouvrir la compétition.

2. Vérifier la compétition de démonstration
   - participants visibles ;
   - participants validés / non validés / retirés ;
   - poules brouillon et figées ;
   - combats générés ;
   - scores saisis ;
   - classement visible.

3. Créer une nouvelle compétition
   - revenir à l'accueil ;
   - créer une compétition ;
   - vérifier qu'elle appartient au club courant ;
   - ouvrir le détail.

4. Inviter des clubs
   - ajouter un club en pré-invité ;
   - marquer l'invitation envoyée ;
   - vérifier que le statut passe à `invite`.

5. Simuler les réponses
   - passer sur le club invité ;
   - confirmer la participation ;
   - revenir organisateur et vérifier le statut ;
   - refaire le scénario avec un autre club et refuser la participation.

6. Inscrire des participants
   - avec un club confirmé, inscrire un participant ;
   - vérifier que le participant est rattaché au club courant ;
   - vérifier que le club ne voit que ses propres participants.

7. Valider / retirer
   - revenir Club A ;
   - valider un participant actif ;
   - retirer un participant ;
   - vérifier que le participant retiré reste visible avec `Participation annulée` ;
   - réactiver si nécessaire.

8. Créer des poules
   - créer une poule brouillon ;
   - vérifier qu'elle apparaît dans `Organisation des poules`.

9. Affecter / déplacer
   - affecter un participant actif et validé ;
   - déplacer vers une autre poule brouillon ;
   - retirer l'affectation.

10. Figer
   - affecter au moins deux participants dans une poule ;
   - figer la poule ;
   - vérifier que les actions de modification d'affectation ne sont plus disponibles.

11. Générer combats
   - générer les combats d'une poule figée ;
   - vérifier que chaque paire apparaît une seule fois.

12. Saisir scores
   - saisir un score ;
   - modifier un score déjà saisi ;
   - vérifier le statut `termine`.

13. Vérifier classement
   - vérifier que tous les participants de la poule apparaissent ;
   - vérifier les points ;
   - vérifier que les combats non terminés sont ignorés.

### Actions impossibles à tester

Vérifier que les messages sont explicites et commencent par :

```text
Impossible : …
```

Cas à tester :
- figer une poule avec moins de deux participants ;
- affecter un participant non validé ;
- affecter un participant retiré ;
- déplacer vers la même poule ;
- modifier un participant validé ;
- retirer un participant dans une poule figée ;
- générer les combats d'une poule non figée ;
- générer les combats une deuxième fois ;
- saisir un score avec un club non organisateur.

Résultat attendu :
- pas d'erreur technique brute ;
- redirection propre si accès direct par URL ;
- message clair ;
- aucune donnée incohérente créée.

## Grille de synthèse

| Point testé | OK | Problème observé | Priorité |
| --- | --- | --- | --- |
| Accueil compréhensible | oui / non |  | faible / moyen / fort |
| Création compétition | oui / non |  | faible / moyen / fort |
| Invitation club | oui / non |  | faible / moyen / fort |
| Réponse invitation | oui / non |  | faible / moyen / fort |
| Inscription participant | oui / non |  | faible / moyen / fort |
| Validation participant | oui / non |  | faible / moyen / fort |
| Poules | oui / non |  | faible / moyen / fort |
| Combats | oui / non |  | faible / moyen / fort |
| Classement | oui / non |  | faible / moyen / fort |
| Messages `Impossible : …` | oui / non |  | faible / moyen / fort |

## Commande de reset retenue

Commande retenue :

```bash
php artisan migrate:fresh --seed
```

Aucune commande `demo:reset` n'est ajoutée pour l'instant : la commande Laravel native est suffisante, explicite et évite d'ajouter du code de maintenance.
