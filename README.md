# ng1-pix-academy

Plugin WordPress pour intégrer et gérer des fonctionnalités liées à Pix Academy (intitulé provisoire). Ce dépôt contient le code du plugin, ses assets d’administration et la configuration nécessaire pour l’installer localement dans un site WordPress.

> Note: Cette documentation est un point de départ. Personnalisez les sections marquées « À adapter » selon vos besoins réels (nom, objectifs précis, dépendances, etc.).

---

## Sommaire

- **Description**
- **Fonctionnalités**
- **Prérequis**
- **Installation**
- **Configuration**
- **Utilisation**
- **Développement**
- **Structure du plugin**
- **Dépannage**
- **Changelog**
- **Licence**

---

## Description

`ng1-pix-academy` est un plugin WordPress conçu pour ajouter des fonctionnalités spécifiques à Pix Academy dans l’administration et/ou le front-office du site. Il s’intègre au thème et peut exposer des scripts et styles dédiés via le dossier `admin/`.

À adapter: décrire précisément ce que fait le plugin (ex: gestion de contenus Pix Academy, intégration d’API, widgets, shortcodes, blocs Gutenberg, etc.).

## Fonctionnalités

- Intégration d’assets d’administration: `admin/css/`, `admin/js/`, `admin/fonts/`, `admin/image/`.
- Point d’entrée du plugin: `ng1-pix-academy.php`.
- À adapter: préciser les écrans d’admin créés, les CPT/Taxonomies, métaboxes, shortcodes, blocs, REST routes, etc.

## Prérequis

- WordPress 6.0+ (recommandé)
- PHP 7.4+ (PHP 8.x conseillé)
- Accès à l’instance WordPress (Local WP, MAMP, LAMP, Docker, etc.)
- Optionnel: Node.js/Yarn/NPM si vous avez un workflow front (non requis par défaut)

## Installation

1. Copier le dossier du plugin dans `wp-content/plugins/ng1-pix-academy/`.
2. Se connecter à l’administration WordPress (`/wp-admin`).
3. Aller dans « Extensions » > rechercher « ng1-pix-academy » > **Activer**.

Mise à jour: pour mettre à jour le plugin, remplacez les fichiers (ou utilisez Git) puis rechargez l’admin.

## Configuration

- À adapter: décrire les pages d’options du plugin, les clés API à renseigner, les capacités utilisateurs nécessaires, etc.
- Si une clé API ou un secret est requis, ne le versionnez pas. Utilisez un fichier `.env` (déjà ignoré via `.gitignore`) ou la configuration serveur.

## Utilisation

- À adapter: expliquer comment accéder aux écrans d’admin, utiliser les shortcodes/blocs, ou intégrer les fonctionnalités sur le front.
- Exemple de shortcode (si applicable):

```php
[pix_academy_example id="123"]
```

## Développement

Si vous souhaitez contribuer ou développer localement:

1. Cloner le dépôt dans `wp-content/plugins/`.
2. Ouvrir le projet dans votre IDE.
3. Éditer le fichier principal `ng1-pix-academy.php` et les assets dans `admin/`.

Workflow front (optionnel):

- Si vous utilisez un bundler (Vite/Webpack/Rollup), ajoutez vos commandes dans un `package.json` et veillez à sortir les build dans `admin/` ou un dossier `dist/` (déjà ignoré par `.gitignore`).

Qualité code (optionnel):

- Vous pouvez ajouter PHP_CodeSniffer, PHPStan, Psalm, ou Prettier/ESLint pour JS/CSS. Les caches communs sont déjà dans `.gitignore`.

## Structure du plugin

```
ng1-pix-academy/
├─ ng1-pix-academy.php         # Fichier principal du plugin (header WP, hooks init, etc.)
├─ README.md                    # Ce fichier
├─ .gitignore                   # Règles d’ignore pour Git
└─ admin/
   ├─ css/                     # Styles d’administration
   ├─ js/                      # Scripts d’administration
   ├─ fonts/                   # Polices (si nécessaire)
   └─ image/                   # Images/illustrations d’admin
```

## Dépannage

- **Le plugin n’apparaît pas dans la liste des extensions**: vérifiez l’en-tête du fichier `ng1-pix-academy.php` (commentaire standard WordPress) et les permissions de fichiers.
- **Erreurs PHP**: activez `WP_DEBUG` dans `wp-config.php` et consultez les logs (`wp-content/debug.log` si activé). Assurez-vous de la version de PHP.
- **Assets qui ne se chargent pas**: vérifiez les chemins d’enqueue (`wp_enqueue_script`, `wp_enqueue_style`) et que les fichiers existent dans `admin/`.

## Changelog

- 0.1.0 — Initialisation du dépôt et structure de base.

## Licence

MIT


