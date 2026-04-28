# Sprint 17 --- Stabilisation UX et cohérence métier

## 🎯 Objectif

Améliorer la lisibilité, la fluidité et la cohérence métier du produit
après test d'usage réel, sans ajouter de nouvelle fonctionnalité.

## ✅ Évolutions principales

### 1. Navigation compétitions unifiée

-   Une seule liste `/competitions`
-   Badge clair : Organisateur / Participant
-   Action : Gérer / Voir

### 2. États visibles des participants

Ordre : 1. Participation annulée 2. Validé / Non validé 3. Poule : nom
4. Poule figée

### 3. Blocage modification participant

Impossible si : - validé - affecté - retiré

### 4. Actions impossibles explicites

Format : Impossible : ...

Exemples : - poule figée - participant validé - minimum 2 participants

## 🧪 Commandes

php artisan migrate:fresh --seed php artisan serve --port=8001 php
artisan test

## 🔁 Flux complet

inscription → validation → poules → affectation → figement → combats →
scores → classement

## ✅ Résultat

Produit stable, cohérent et utilisable terrain.
