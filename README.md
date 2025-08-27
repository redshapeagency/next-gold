# Next Gold - Gestionale per Compro Oro

Un'applicazione web completa per la gestione di un negozio di compro oro, sviluppata con Laravel 11, PostgreSQL, Redis e TailwindCSS.

## Caratteristiche

- **Dashboard** con KPI e monitoraggio prezzo oro in tempo reale
- **Gestione Clienti** con ricerca e storico documenti
- **Magazzino** con categorie, materiali e foto prodotti
- **Documenti** (Acquisti/Vendite) con generazione PDF
- **Archivio** prodotti venduti
- **Impostazioni** complete con gestione utenti e ruoli
- **Backup/Restore** con firma HMAC
- **Autenticazione** con username/email e ruoli (admin/operator/viewer)
- **Setup Wizard** per primo accesso

## Stack Tecnologico

- **Backend**: PHP 8.4, Laravel 11
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis 7
- **Frontend**: Blade + TailwindCSS + Alpine.js
- **Asset**: Vite, Node 20
- **Sicurezza**: Rate limiting, CSRF, Content Security Policy
- **Qualità**: Laravel Pint, PHPStan, PHPUnit

## Installazione con Docker

### Prerequisiti

- Docker
- Docker Compose

### Setup

1. **Clona il repository**
   ```bash
   git clone https://github.com/redshapeagency/next-gold.git
   cd next-gold
   ```

2. **Avvia i container**
   ```bash
   docker-compose up -d
   ```

3. **Installa dipendenze**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

4. **Configura ambiente**
   ```bash
   docker-compose exec app cp .env.example .env
   docker-compose exec app php artisan key:generate
   ```

5. **Esegui migrazioni**
   ```bash
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

6. **Accedi**
   - Applicazione: `http://localhost:8000`
   - MailHog: `http://localhost:8025`

## Deploy su Ubuntu 24.04

### Installazione Automatica

```bash
# Scarica ed esegui lo script di installazione
curl -fsSL https://raw.githubusercontent.com/redshapeagency/next-gold/main/scripts/install.sh | bash
```

### Installazione Manuale

1. **Prepara il server**
   ```bash
   # Aggiorna sistema
   sudo apt update && sudo apt upgrade -y

   # Installa PHP 8.4 e estensioni
   sudo apt install software-properties-common -y
   sudo add-apt-repository ppa:ondrej/php -y
   sudo apt update
   sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-pgsql php8.4-redis php8.4-mbstring php8.4-intl php8.4-gd php8.4-bcmath php8.4-xml php8.4-curl php8.4-dom php8.4-zip php8.4-fileinfo -y

   # Installa PostgreSQL 16
   sudo apt install postgresql-16 postgresql-contrib-16 -y

   # Installa Redis 7
   sudo apt install redis-server -y

   # Installa Nginx
   sudo apt install nginx -y

   # Installa Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer

   # Installa Node.js 20
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt-get install -y nodejs

   # Installa Certbot (opzionale per SSL)
   sudo apt install certbot python3-certbot-nginx -y
   ```

2. **Configura PostgreSQL**
   ```bash
   sudo -u postgres createuser --interactive --pwprompt next_gold_user
   sudo -u postgres createdb -O next_gold_user next_gold
   ```

3. **Clona e configura applicazione**
   ```bash
   cd /var/www
   sudo git clone https://github.com/redshapeagency/next-gold.git
   sudo chown -R www-data:www-data next-gold
   cd next-gold

   # Installa dipendenze
   sudo -u www-data composer install --no-dev --optimize-autoloader
   sudo -u www-data npm ci && sudo -u www-data npm run build

   # Configura ambiente
   sudo -u www-data cp .env.example .env
   sudo -u www-data php artisan key:generate

   # Aggiorna .env con credenziali database e altre configurazioni
   ```

4. **Configura Nginx**
   ```bash
   # Crea configurazione sito
   sudo cp scripts/nginx/next-gold.conf.tpl /etc/nginx/sites-available/next-gold
   # Modifica il template con i tuoi valori
   sudo ln -s /etc/nginx/sites-available/next-gold /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

5. **Configura SSL (opzionale)**
   ```bash
   sudo certbot --nginx -d yourdomain.com
   ```

6. **Configura queue e scheduler**
   ```bash
   # Crea servizio systemd per queue
   sudo cp scripts/systemd/next-gold-queue.service /etc/systemd/system/
   sudo systemctl enable next-gold-queue
   sudo systemctl start next-gold-queue

   # Aggiungi cron per scheduler
   sudo crontab -e
   # Aggiungi: * * * * * php /var/www/next-gold/artisan schedule:run
   ```

7. **Finalizza setup**
   ```bash
   sudo -u www-data php artisan migrate --force
   sudo -u www-data php artisan db:seed --force
   sudo -u www-data php artisan storage:link
   ```

## Script di Gestione

Il progetto include diversi script per la gestione del sistema:

### Controlli di Sistema
```bash
# Verifica stato di tutti i servizi
./scripts/checks.sh
```

### Backup
```bash
# Crea backup con firma HMAC
./scripts/backup.sh

# Ripristina da backup
./scripts/restore.sh /path/to/backup.tar.gz
```

### Aggiornamenti
```bash
# Aggiorna applicazione con zero-downtime
./scripts/update.sh
```

### Monitoraggio
```bash
# Controlla salute sistema e servizi
./scripts/monitor.sh
```

### Installazione Automatica

```bash
# Scarica ed esegui lo script di installazione
curl -fsSL https://raw.githubusercontent.com/redshapeagency/next-gold/main/install.sh | bash
```

### Installazione Manuale

1. **Prepara il server**
   ```bash
   # Aggiorna sistema
   sudo apt update && sudo apt upgrade -y

   # Installa PHP 8.4 e estensioni
   sudo apt install software-properties-common -y
   sudo add-apt-repository ppa:ondrej/php -y
   sudo apt update
   sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-pgsql php8.4-redis php8.4-mbstring php8.4-intl php8.4-gd php8.4-bcmath php8.4-xml php8.4-curl php8.4-dom php8.4-zip php8.4-fileinfo -y

   # Installa PostgreSQL 16
   sudo apt install postgresql-16 postgresql-contrib-16 -y

   # Installa Redis 7
   sudo apt install redis-server -y

   # Installa Nginx
   sudo apt install nginx -y

   # Installa Composer
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer

   # Installa Node.js 20
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt-get install -y nodejs

   # Installa Certbot (opzionale per SSL)
   sudo apt install certbot python3-certbot-nginx -y
   ```

2. **Configura PostgreSQL**
   ```bash
   sudo -u postgres createuser --interactive --pwprompt next_gold_user
   sudo -u postgres createdb -O next_gold_user next_gold
   ```

3. **Clona e configura applicazione**
   ```bash
   cd /var/www
   sudo git clone https://github.com/redshapeagency/next-gold.git
   sudo chown -R www-data:www-data next-gold
   cd next-gold

   # Installa dipendenze
   sudo -u www-data composer install --no-dev --optimize-autoloader
   sudo -u www-data npm ci && sudo -u www-data npm run build

   # Configura ambiente
   sudo -u www-data cp .env.example .env
   sudo -u www-data php artisan key:generate

   # Aggiorna .env con credenziali database e altre configurazioni
   ```

4. **Configura Nginx**
   ```bash
   # Crea configurazione sito
   sudo cp scripts/nginx/next-gold.conf.tpl /etc/nginx/sites-available/next-gold
   # Modifica il template con i tuoi valori
   sudo ln -s /etc/nginx/sites-available/next-gold /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

5. **Configura SSL (opzionale)**
   ```bash
   sudo certbot --nginx -d yourdomain.com
   ```

6. **Configura queue e scheduler**
   ```bash
   # Crea servizio systemd per queue
   sudo cp scripts/systemd/next-gold-queue.service /etc/systemd/system/
   sudo systemctl enable next-gold-queue
   sudo systemctl start next-gold-queue

   # Aggiungi cron per scheduler
   sudo crontab -e
   # Aggiungi: * * * * * php /var/www/next-gold/artisan schedule:run
   ```

7. **Finalizza setup**
   ```bash
   sudo -u www-data php artisan migrate --force
   sudo -u www-data php artisan db:seed --force
   sudo -u www-data php artisan storage:link
   ```

## Utilizzo

### Primo Accesso

1. Vai all'URL dell'applicazione
2. Verrai reindirizzato al setup wizard
3. Crea il primo utente amministratore
4. Accedi con le credenziali create

### Gestione Prezzo Oro

- Configura l'API nelle **Impostazioni > Prezzo Oro/API**
- Il sistema recupera automaticamente i prezzi ogni 60 secondi
- I prezzi sono cachati in Redis per prestazioni ottimali

### Backup e Restore

- **Export**: Genera backup JSON firmato con HMAC
- **Import**: Supporta modalità "append" o "replace"
- I backup includono tutti i dati critici

## Comandi Artisan

```bash
# Recupera prezzo oro
php artisan gold:fetch

# Genera backup
php artisan backup:export

# Importa backup
php artisan backup:import /path/to/backup.json --mode=append
```

## Test

```bash
# Esegui tutti i test
php artisan test

# Esegui solo test unit
php artisan test --testsuite=Unit

# Esegui solo test feature
php artisan test --testsuite=Feature
```

## Sicurezza

- Autenticazione robusta con rate limiting
- Autorizzazioni basate su ruoli con Spatie Permission
- Sanitizzazione input e validazioni
- Headers sicuri Nginx
- Content Security Policy
- Log di sicurezza per accessi e azioni

## Monitoraggio

- Log di accesso (`login_logs`)
- Log delle azioni (`action_logs`)
- Monitoraggio prezzo oro in tempo reale
- KPI dashboard

## Licenza

Questo progetto è distribuito sotto licenza MIT.

## Supporto

Per supporto o domande, apri una issue su GitHub.
