# Jeu de test demo

Ce document indique quelle competition utiliser pour tester rapidement chaque zone du MVP FFK Interclubs.

## Reset de la demo

Commande recommandee :

```bash
php artisan demo:reset --password="mot-de-passe"
```

Avant utilisation, renseigner dans `.env` :

```dotenv
DEMO_RESET_PASSWORD=mot-de-passe
```

La commande :
- refuse de s'executer si `APP_ENV=production` ;
- demande un mot de passe ;
- supprime les donnees ;
- relance les migrations ;
- recharge les seeders ;
- remet la demo dans un etat stable.

Alternative technique si besoin, sans protection :

```bash
php artisan migrate:fresh --seed
```

Utiliser le reset :
- avant une session de test externe ;
- apres des manipulations destructrices ;
- si les poules, combats ou scores ont ete trop modifies pendant une demo.

## Comptes utiles

Les utilisateurs de demo sont lies a leur club.

- `karate-club-paris-centre@example.test` : Karaté Club Paris Centre
- `dojo-shotokan-lyon@example.test` : Dojo Shotokan Lyon
- `karate-club-marseille-sud@example.test` : Karaté Club Marseille Sud
- `dojo-bushido-bordeaux@example.test` : Dojo Bushido Bordeaux
- `karate-club-lille-metropole@example.test` : Karaté Club Lille Métropole
- `kc-marseille-13@example.test` : KC Marseille 13
- `karate-club-provence@example.test` : Karaté Club Provence
- `budokan-nice@example.test` : Budokan Nice
- `as-karate-rennes@example.test` : AS Karaté Rennes
- `dojo-nantais@example.test` : Dojo Nantais
- `sen-no-sen-strasbourg@example.test` : Sen No Sen Strasbourg
- `jc-vaulx-en-velin@example.test` : JC Vaulx-en-Velin

Pour changer de compte, utiliser la page `Changer d'utilisateur`.

## Cartographie rapide

| Fonctionnalite a tester | Competition conseillee | Club / utilisateur conseille | Remarque |
| --- | --- | --- | --- |
| Assistant de generation intelligente de poules | Open Interclubs Méditerranée 2026 | KC Marseille 13 | Beaucoup de participants valides, actifs et non affectes. Aucun combat ni poule creee au depart. |
| Creation manuelle de poules | Open Interclubs Méditerranée 2026 | KC Marseille 13 | Creer une ou plusieurs poules avant d'affecter par clic ou drag & drop. |
| Affectation participant par clic | Open Interclubs Méditerranée 2026 | KC Marseille 13 | Creer une poule en preparation, puis cliquer un participant disponible. |
| Drag & drop | Open Interclubs Méditerranée 2026 | KC Marseille 13 | Meme contexte que l'affectation par clic. |
| Figer / defiger une poule | Coupe de la Joie | Karaté Club Paris Centre | Contient des poules figees et une poule en preparation. |
| Generation des combats | Open Interclubs Méditerranée 2026 ou Coupe de la Joie | KC Marseille 13 ou Karaté Club Paris Centre | Sur Open Mediterranee, creer/affecter/figer d'abord. Sur Coupe, utiliser une poule en preparation. |
| Saisie des resultats | Coupe de la Joie | Karaté Club Paris Centre | Combats partiellement saisis, pratique pour tester les scores restants. |
| Modification d'un resultat | Coupe de la Joie | Karaté Club Paris Centre | Plusieurs combats deja termines. |
| Classement | Interclubs Samouraï 2026 | Karaté Club Paris Centre | Competition complete avec poules figees, combats saisis et classements visibles. |
| Impression feuille combats | Coupe de la Joie ou Interclubs Samouraï 2026 | Karaté Club Paris Centre | Coupe permet de voir des combats saisis et non saisis. Samourai donne un cas complet. |
| Impression resultat poule | Interclubs Samouraï 2026 | Karaté Club Paris Centre | Poules completes avec classements. |
| Participants en attente | Open Interclubs Méditerranée 2026 | KC Marseille 13 | Contient plusieurs participants non valides. |
| Participants retires / reintegration | Open Interclubs Méditerranée 2026 | KC Marseille 13 | Contient quelques participants retires. |
| Clubs invites / confirmes | Open Interclubs Méditerranée 2026 | KC Marseille 13 puis clubs invites | Plusieurs clubs confirmes et plusieurs clubs encore invites. |
| Changement utilisateur organisateur / club invite | Open Interclubs Méditerranée 2026 | KC Marseille 13 puis Dojo Shotokan Lyon, Sen No Sen Strasbourg | Permet de comparer organisateur, participant confirme et invite. |

## Competitions de demonstration

### Open Interclubs Méditerranée 2026

- Organisateur : KC Marseille 13
- Date : 14/06/2026
- Etat : inscriptions ouvertes
- Clubs confirmes : Dojo Shotokan Lyon, Karaté Club Provence, Dojo Bushido Bordeaux, Budokan Nice, AS Karaté Rennes, Dojo Nantais
- Clubs invites en attente : Karaté Club Lille Métropole, Sen No Sen Strasbourg, JC Vaulx-en-Velin
- Donnees : environ 40 participants, dont une large majorite valides et non affectes.

Fonctionnalites testables :
- assistant de generation de poules ;
- criteres sexe / age / poids / taille cible ;
- participants mineurs proches de l'adulte ;
- participants non affectes ;
- creation manuelle de poules ;
- affectation par clic ;
- drag & drop ;
- participants en attente ;
- participants retires / reintegration ;
- changement d'utilisateur organisateur / invite.

Remarques :
- C'est la competition principale pour tester l'assistant.
- Au reset, aucune poule n'est creee automatiquement pour cette competition.
- Pour tester l'affectation par clic ou drag & drop, creer d'abord une poule.

### Interclubs Samouraï 2026

- Organisateur : Karaté Club Paris Centre
- Etat : competition complete
- Clubs participants : Dojo Shotokan Lyon, Karaté Club Marseille Sud, Dojo Bushido Bordeaux
- Club invite : Karaté Club Lille Métropole
- Donnees : plusieurs poules figees avec combats saisis.

Fonctionnalites testables :
- classements complets ;
- impression resultat poule ;
- impression feuille combats ;
- lecture des poules figees ;
- modification de resultat si besoin ;
- verification d'une competition stable de bout en bout.

Remarques :
- C'est le meilleur cas pour presenter une competition deja terminee ou presque terminee.

### Coupe de la Joie

- Organisateur : Karaté Club Paris Centre
- Etat : competition en cours
- Clubs confirmes : Dojo Shotokan Lyon, Karaté Club Marseille Sud, Karaté Club Lille Métropole
- Club invite : Dojo Bushido Bordeaux
- Donnees : poules figees avec scores partiels, plus une poule en preparation.

Fonctionnalites testables :
- saisie de resultats ;
- modification d'un resultat ;
- combats restants ;
- figer une poule en preparation ;
- defiger une poule ;
- generation de combats apres figement ;
- classement partiel.

Remarques :
- Bon terrain pour tester les transitions entre preparation, combats et classement.

### Open Dojo National

- Organisateur : Karaté Club Paris Centre
- Etat : debut / donnees legeres
- Clubs confirmes : Dojo Shotokan Lyon, Dojo Bushido Bordeaux
- Clubs invites ou pre-invites : Karaté Club Marseille Sud, Karaté Club Lille Métropole
- Donnees : peu de participants, une poule figee sans score et une poule en constitution.

Fonctionnalites testables :
- debut de competition ;
- invitations non finalisees ;
- poules peu remplies ;
- generation / absence de scores ;
- affichage des etats vides.

Remarques :
- Utile pour tester les messages de guidage et les cas incomplets.

### Challenge des Dojos 2026

- Organisateur : Karaté Club Paris Centre
- Etat : competition passee
- Donnees : poules figees avec scores complets.

Fonctionnalites testables :
- affichage des competitions passees ;
- classements complets ;
- impression resultat poule ;
- comparaison avec les competitions a venir.

Remarques :
- Utile pour verifier la separation "a venir / passees" dans la liste des competitions.

### Trophée Kata Kumité

- Organisateur : Karaté Club Paris Centre
- Etat : competition a venir, scores partiels ou absents selon poule
- Donnees : deux poules figees, une avec scores partiels, une sans score.

Fonctionnalites testables :
- saisie de scores ;
- modification de resultats ;
- classement avec poule non terminee ;
- impression feuille combats.

Remarques :
- Bon cas intermediaire pour tester la page Combats sans repartir de zero.

### Rencontre Interclubs Aquitaine

- Organisateur : Karaté Club Paris Centre
- Date : aujourd'hui dans le seed
- Etat : competition du jour
- Donnees : une poule figee sans score, une poule en preparation.

Fonctionnalites testables :
- competition datee du jour ;
- poule figee sans resultats ;
- saisie de tous les combats ;
- poule en preparation ;
- affichage dans les competitions a venir.

Remarques :
- Utile pour tester les cas "jour J".

## Parcours de test recommande

1. Lancer le reset demo.
2. Se connecter comme `kc-marseille-13@example.test`.
3. Ouvrir `Open Interclubs Méditerranée 2026`.
4. Tester l'assistant de generation des poules.
5. Creer une poule manuellement.
6. Affecter un participant par clic.
7. Affecter un participant par drag & drop.
8. Changer d'utilisateur vers un club invite pour verifier la lecture seule ou l'action d'invitation.
9. Revenir comme `karate-club-paris-centre@example.test`.
10. Ouvrir `Coupe de la Joie` pour tester combats, resultats et defigement.
11. Ouvrir `Interclubs Samouraï 2026` pour tester classement et impressions.

## Points de vigilance

- Les IDs des competitions peuvent changer apres reset : utiliser les noms, pas les IDs.
- Les actions destructrices de test peuvent modifier durablement la demo : relancer le reset si besoin.
- Le reset protege est volontairement en commande Artisan, pas en bouton web, pour eviter une suppression accidentelle depuis l'interface.
