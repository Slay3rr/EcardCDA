# Documentation de Déploiement

Ce guide détaille les étapes nécessaires pour déployer l'application en production de manière reproductible et sécurisée.

## Prérequis

- **Accès au dépôt Git** : Assurez-vous de pouvoir cloner le dépôt à partir de `https://github.com/Slay3rr/EcardCDA.git`.
- **Accès au serveur** : Disposer des droits nécessaires pour écrire dans le dossier de destination (par exemple, `/var/www/html`).
- **Environnement PHP et Composer** : PHP et Composer doivent être installés et configurés sur le serveur ou la machine de déploiement.

## Étapes de Déploiement

1. **Préparation de l'Environnement**  
   - Supprimez l'ancien build (si présent) afin d'éviter les conflits.
   - Clonez le dépôt depuis la branche `main` dans un répertoire temporaire.
   ```bash
   rm -rf web006
   git clone -b main https://github.com/Slay3rr/EcardCDA.git web006
   ```

2. **Installation des Dépendances**  
   - Passez dans le répertoire cloné et installez les dépendances via Composer.
   ```bash
   cd web006
   composer install --optimize-autoloader
   ```

3. **Configuration JWT**  
   - Créez le répertoire pour les clés JWT et générez les clés publique et privée nécessaires pour l'authentification.
   ```bash
   mkdir -p config/jwt
   php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=prod
   chmod -R 644 config/jwt/*
   ```

4. **Configuration de l'Environnement**  
   - Mettez à jour ou créez le fichier `.env.local` avec les variables spécifiques de production (base de données, MongoDB, CORS, etc.).

5. **Migration de la Base de Données**  
   - Créez la base de données (si elle n'existe pas) et appliquez les migrations pour mettre à jour le schéma.
   ```bash
   php bin/console doctrine:database:create --if-not-exists --env=prod
   php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod
   ```

6. **Nettoyage et Réchauffement du Cache**  
   - Effacez le cache de l'application et effectuez un préchauffage pour améliorer les performances.
   ```bash
   php bin/console cache:clear --env=prod
   php bin/console cache:warmup
   ```

7. **Déploiement du Code**  
   - Supprimez l'ancien dossier de production, recréez-le, puis copiez le build mis à jour sur le serveur avec les permissions appropriées.
   ```bash
   rm -rf /var/www/html/web006
   mkdir /var/www/html/web006
   cp -rT web006 /var/www/html/web006
   chmod -R 775 /var/www/html/web006/var
   ```

## Conclusion

Ce processus de déploiement détaillé permet de garantir une mise en production fluide et sécurisée. Il est recommandé de suivre ces étapes à la lettre pour maintenir la stabilité de l'application et faciliter la gestion des futurs déploiements.