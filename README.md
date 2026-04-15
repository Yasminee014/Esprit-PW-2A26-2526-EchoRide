# Module Réclamations — EcoRide
## Structure MVC + POO + PDO

```
ecoride/
├── Model/
│   ├── Database.php              ← Connexion PDO Singleton
│   └── ReclamationModel.php      ← CRUD complet (PDO)
│
├── Controller/
│   ├── ReclamationController.php       ← BackOffice (Admin)
│   └── ReclamationFrontController.php  ← FrontOffice (Utilisateur)
│
├── View/
│   ├── backoffice/
│   │   └── admin_reclamations.php  ← Vue admin (dashboard)
│   └── frontoffice/
│       └── mes_reclamations.php    ← Vue utilisateur
│
└── reclamations.sql               ← Script SQL (table + données test)
```

## Contraintes respectées

| Contrainte | Statut |
|---|---|
| ✅ Pas de validation HTML5 (`required`, `pattern`…) | Validation JS pure + PHP serveur |
| ✅ Architecture MVC | Model / View / Controller séparés |
| ✅ Programmation Orientée Objet | Classes, constructeurs, méthodes |
| ✅ PDO obligatoire | Singleton PDO, requêtes préparées |
| ✅ Contrôle de saisie fonctionnel | JS côté client + PHP côté serveur |
| ✅ FrontOffice + BackOffice | Deux interfaces distinctes |
| ✅ CRUD complet | Create / Read / Update / Delete |

## Intégration dans le projet

### 1. Importer la table SQL
```sql
-- Dans phpMyAdmin ou CLI MySQL :
SOURCE /chemin/vers/reclamations.sql;
```

### 2. Configurer la connexion dans `Model/Database.php`
```php
$host   = 'localhost';
$dbname = 'ecoride';    // ← votre base
$user   = 'root';       // ← votre utilisateur
$pass   = '';           // ← votre mot de passe
```

### 3. Ajouter les liens dans la navigation
- **Sidebar admin** : lien vers `admin_reclamations.php`
- **Navbar frontoffice** : lien vers `mes_reclamations.php`

### 4. Point d'entrée BackOffice
Créer `admin_reclamations.php` à la racine du backoffice :
```php
<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: login.php'); exit; }
require_once '../../Controller/ReclamationController.php';
(new ReclamationController())->handleRequest();
```

### 5. Point d'entrée FrontOffice
Créer `mes_reclamations.php` à la racine du frontoffice :
```php
<?php
session_start();
require_once '../../Controller/ReclamationFrontController.php';
(new ReclamationFrontController())->handleRequest();
```

## Fonctionnalités

### BackOffice (Admin)
- 📊 Tableau de bord avec compteurs par statut
- 🔍 Recherche en temps réel (titre, utilisateur)
- 🔽 Filtres : statut, priorité, catégorie
- ➕ Ajouter une réclamation (modale)
- ✏️ Modifier une réclamation (modale)
- 👁️ Voir le détail et répondre (modale)
- 🔄 Changer le statut directement dans la table
- 🗑️ Supprimer une réclamation

### FrontOffice (Utilisateur)
- 📋 Liste de ses réclamations avec statuts et réponses admin
- ➕ Formulaire de soumission (colonne droite sticky)
- ✅ Validation JS complète sans HTML5
- 📊 Compteur de caractères sur la description
- 🎨 Design cohérent avec le reste de l'application EcoRide
