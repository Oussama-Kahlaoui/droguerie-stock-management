# Système de Gestion de Stock pour Droguerie

Un système complet de gestion de stock développé en PHP pour les drogueries, permettant de gérer efficacement l'inventaire, les ventes, les fournisseurs et le suivi des produits.

## 🌟 Fonctionnalités

### 📊 Tableau de Bord
- Vue d'ensemble en temps réel des statistiques
- Suivi des ventes quotidiennes
- Alertes de stock faible
- Suivi des produits expirés
- Graphiques interactifs pour les ventes et l'inventaire
- Activités récentes

### 📦 Gestion des Produits
- Ajout, modification et suppression de produits
- Suivi des quantités en stock
- Gestion des dates d'expiration
- Catégorisation des produits
- Alertes de stock faible
- Historique des modifications

### 💰 Gestion des Ventes
- Enregistrement des ventes
- Suivi des transactions
- Statistiques de ventes par période
- Meilleurs produits vendus
- Historique des ventes
- Rapports de performance

### 🚛 Gestion des Fournisseurs
- Gestion des fournisseurs
- Suivi des livraisons
- Statistiques par fournisseur
- Valeur totale des produits par fournisseur
- Alertes de stock par fournisseur

## 🛠️ Technologies Utilisées

- PHP 7.4+
- MySQL/MariaDB
- HTML5/CSS3
- JavaScript (ES6+)
- Chart.js pour les visualisations
- jQuery pour les interactions AJAX

## 📋 Prérequis

- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PHP requises :
  - PDO
  - PDO_MySQL
  - JSON
  - GD (pour les graphiques)

## 🚀 Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/votre-username/droguerie-stock-management.git
```

2. Configurez votre serveur web pour pointer vers le répertoire du projet

3. Créez une base de données MySQL et importez le fichier `database.sql`

4. Configurez la connexion à la base de données dans `db.php`

5. Assurez-vous que les permissions des dossiers sont correctement configurées :
```bash
chmod 755 -R /chemin/vers/le/projet
chmod 777 -R /chemin/vers/le/projet/uploads
```

## 🔧 Configuration

1. Modifiez le fichier `db.php` avec vos informations de connexion :
```php
$host = 'localhost';
$dbname = 'votre_base_de_donnees';
$username = 'votre_utilisateur';
$password = 'votre_mot_de_passe';
```

2. Configurez les paramètres de l'application dans `config.php` si nécessaire

## 🔐 Sécurité

- Authentification utilisateur sécurisée
- Protection contre les injections SQL
- Validation des entrées utilisateur
- Gestion des sessions sécurisée
- Protection XSS

## 📱 Interface Responsive

- Design adaptatif pour tous les appareils
- Interface utilisateur intuitive
- Navigation facile
- Visualisations optimisées pour mobile

## 🔄 Mises à jour en Temps Réel

- Actualisation automatique des données
- Notifications en temps réel
- Suivi des modifications
- Historique des activités

## 📈 Rapports et Statistiques

- Rapports de ventes détaillés
- Analyses de stock
- Statistiques de performance
- Graphiques interactifs
- Export de données

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :

1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commiter vos changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👥 Auteurs

- Votre Nom - Développement initial

## 🙏 Remerciements

- Chart.js pour les visualisations
- Font Awesome pour les icônes
- jQuery pour les interactions AJAX

## 📞 Support

Pour toute question ou problème, veuillez ouvrir une issue dans le dépôt GitHub.
