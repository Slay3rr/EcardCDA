pipeline {
    agent any

    environment {
        GIT_REPO = "https://github.com/Slay3rr/EcardCDA.git"
        GIT_BRANCH = "main"
        DEPLOY_DIR = "web006"
    }

    stages {
        stage('Cloner le dépôt') { 
            steps {
                sh "rm -rf ${DEPLOY_DIR}" // Nettoyage du précédent build
                sh "git clone -b ${GIT_BRANCH} ${GIT_REPO} ${DEPLOY_DIR}"
            }
        }
        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --optimize-autoloader'
                }
            }
        }

        stage('Configuration JWT') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'mkdir -p config/jwt'
                    sh 'php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=prod'
                    sh 'chmod -R 644 config/jwt/*'

                }
            }
        }

        stage('Configuration de l\'environnement') {
            steps {
                script {
                    def envLocal = """
APP_ENV=prod
APP_DEBUG=1
DATABASE_URL=mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4

# MongoDB Atlas Configuration
MONGODB_URL=mongodb+srv://root:root@cluster0.xcexe.mongodb.net/?retryWrites=true&w=majority
MONGODB_DB=images

# Configuration JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=Tamao

# Configuration CORS
CORS_ALLOW_ORIGIN='^https?://(localhost|127\\.0\\.0\\.1|web006\\.azure\\.certif\\.academy)(:[0-9]+)?\\\$'
""".stripIndent()

                    writeFile file: "${DEPLOY_DIR}/.env.local", text: envLocal
                }
            }
        }

        stage('Migration de la base de données') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=prod'
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod'
                }
            }
        }

        stage('Nettoyage du cache') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console cache:clear --env=prod'
                    sh 'php bin/console cache:warmup'
                }
            }
        }

        stage('Déploiement') {
            steps {
                sh "rm -rf /var/www/html/${DEPLOY_DIR}" // Supprime le dossier de destination
                sh "mkdir /var/www/html/${DEPLOY_DIR}" // Recréé le dossier de destination
                sh "cp -rT ${DEPLOY_DIR} /var/www/html/${DEPLOY_DIR}"
                sh "chmod -R 775 /var/www/html/${DEPLOY_DIR}/var"
            }
        }
    }

    post {
        success {
            echo 'Déploiement réussi !'
        }
        failure {
            echo 'Erreur lors du déploiement.'
        }
    }
}
