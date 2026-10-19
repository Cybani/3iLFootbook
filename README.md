# 🏟️ 3iLFootBook 

Application web de gestion et réservation de terrains de sport pour les étudiants de 3iL Ingénieurs à Limoges.

---

## 🎯 Objectif

Permettre aux étudiants de 3iL de s'inscrire, se connecter et réserver des terrains de sport disponibles (foot, five, etc.) sur différents créneaux horaires.

Seuls les étudiants disposant d'un email `@3il.fr` peuvent accéder à l'application.

---

## ⚙️ Prérequis

| Outil | Utilité |
|-------|---------|
| Git | Récupérer le projet |
| XAMPP | Serveur Apache + PHP en local |
| PostgreSQL | Base de données |
| DBeaver | Interface graphique pour la BDD |

---

## 📥 Récupération du projet
Dans un terminal tapez la commande suivante :

```bash
git clone https://github.com/Cybani/3iLFootbook
```

Placer le dossier cloné `3ilFootBook` dans le répertoire `htdocs` de XAMPP :

- **Windows** : `C:\xampp\htdocs\3iLFootBook\`
- **Linux / Mac** : `/opt/lampp/htdocs/3iLFootBook/`

---

## 🗄️ Configuration de la base de données

### Étape 1 — Créer la base de données

**Via DBeaver (interface graphique) :**

1. Ouvrir DBeaver
2. Dans le panneau en haut à gauche, faire un clic droit sur **Databases**
3. Cliquer sur **Create New Database Connection**
4. Nommer la connexion : `3ilfootbook`
5. Choisir PostgreSQL comme langage de communication à la base de donnée
6. Valider jusqu'à mettre votre identifiant et mdp local qui vous servira pour la suite

7. Ensuite faite Clique droit sur la connexion `3ilfootbook`  -> SQL Editor -> New SQL script et copier colller le script contenu dans :

```
bdd.sql
```

Exécuté le script en cliquant sur l'icône du parchemin avec la flèche orange vers la droite. 
Vous devriez voir les 3 tables du projet `utilisateur`, `terrain` et `reservation ` en déroulant les menus suivant Databases -> postgres -> Schémas -> public - Tables

### Étape 2 — Configurer la connexion PHP

Ouvrir le fichier de configuration `php/bdd.php` et renseigner vos identifiants PostgreSQL :

```php
$host     = 'localhost';
$dbname   = 'postgres';
$user     = 'VOTRE_UTILISATEUR';
$password = 'VOTRE_MOT_DE_PASSE';
```

> 💡 Les identifiants dépendent de votre installation PostgreSQL locale.

---

## 🚀 Lancer l'application

1. Démarrer XAMPP et s'assurer qu'Apache est actif
```
En vous rendant dans XAMPP Control Panel cliquer sur le bouton Start pour démarrer le service Apache
```
2. Ouvrir un navigateur et accéder à :
```
http://localhost/3iLFootBook/accueil.php
```

👉 La page de connexion devrait être accessible, inscrivez vous ou connectez vous pour pour réserver un terrain 😄

   En cas de problème, contactez l'administrateur du site 😉

## 👤 Fonctionnalités

### Administrateur

- Se connecter / déconnecter
- Voir la liste des utilisateurs inscrits
- Ajouter / supprimer des terrains
- Mettre un terrain en maintenance (non réservable)
- Consulter toutes les réservations
- Annuler n'importe quelle réservation

### Utilisateur

- S'inscrire (email `@3il.fr` requis)
- Se connecter / déconnecter
- Consulter la liste des terrains disponibles
- Réserver un terrain sur un créneau libre
- Annuler sa propre réservation

### Système

- Vérification de l'authentification
- Gestion des droits Admin / Utilisateur
- Détection des doublons et chevauchements de réservations

---
## ❗ Remarques importantes

- Le services Postgres doit être lancé sur votre machine sinon la connexion à la Base de données sera impossible
- Le service Apache doit être lancé dans XAMPP Control Panel pour accéder via un navigateur en localhost au site 

---
## 🔄  Rappel - Fonctionnement global

```
Étudiant → Interface web (HTML/CSS) → PHP → PostgreSQL
```

L'utilisateur interagit via l'interface web.  
PHP gère la logique métier et les requêtes vers la base de données.  
PostgreSQL stocke les utilisateurs, les terrains et les réservations.

---

## 🤖 Utilisation de l’intelligence artificielle

Notre démarche est présentée dans la [note sur l’utilisation de l’IA](https://github.com/Cybani/3iLFootbook/releases/tag/note-ia-v1)

## 👥 Projet réalisé par

- ABDALLAH Ines
- BERRAHMOUNE Rayan
- CUBAUT Baptiste
- ROSIER Matthias
- YATOU Mike 

Groupe I1FE — 3iL Ingénieurs 2025-2026
