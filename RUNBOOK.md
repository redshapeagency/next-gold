# Next Gold - Runbook Operativo

Guida per l'operatività quotidiana, manutenzione e troubleshooting dell'applicazione Next Gold.

## Monitoraggio Sistema

### Controlli Giornalieri

```bash
# Verifica stato servizi
sudo systemctl status nginx
sudo systemctl status postgresql
sudo systemctl status redis-server
sudo systemctl status next-gold-queue

# Controlla spazio disco
df -h

# Monitora processi PHP
ps aux | grep php

# Verifica log applicazione
tail -f /var/log/next-gold/laravel.log
```

### Metriche Chiave

- **Prezzo Oro**: Deve aggiornarsi ogni 60 secondi
- **Queue Jobs**: Non dovrebbero accumularsi
- **Database Connections**: Monitorare connessioni attive
- **Spazio Storage**: Per foto prodotti e backup

## Operazioni Quotidiane

### Aggiornamento Prezzo Oro

```bash
# Manuale fetch prezzo
sudo -u www-data php /var/www/next-gold/artisan gold:fetch

# Verifica cache Redis
redis-cli get gold:latest
```

### Backup Database

```bash
# Backup completo
sudo -u www-data php /var/www/next-gold/artisan backup:export

# Backup PostgreSQL nativo
sudo -u postgres pg_dump next_gold > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Script di Gestione

Il sistema include script automatizzati per operazioni comuni:

### Controlli di Sistema

```bash
# Esegue controlli completi di sistema
cd /var/www/next-gold
./scripts/checks.sh
```

Questo script verifica:
- Versione PHP e presenza estensioni
- Stato PostgreSQL e connessione
- Stato Redis e risposta
- Stato Nginx e servizio
- Versione Node.js
- Presenza Composer
- Directory applicazione e permessi
- Porte aperte
- Certificato SSL (se configurato)

### Backup Automatico

```bash
# Crea backup con firma HMAC
./scripts/backup.sh
```

Caratteristiche:
- Crea archivio tar.gz dell'applicazione
- Genera firma HMAC per verifica integrità
- Esclude file non necessari (log, cache, node_modules)
- Pulisce backup vecchi (mantiene ultimi 7 giorni)
- Supporta notifiche email (se configurate)

### Restore da Backup

```bash
# Ripristina da backup specifico
./scripts/restore.sh next-gold_20240101_120000.tar.gz
```

Processo:
- Verifica firma HMAC del backup
- Estrae archivio in directory temporanea
- Ferma servizi (nginx, queue)
- Backup applicazione corrente
- Sposta nuova versione
- Ripristina database
- Pulisce cache
- Riavvia servizi
- Invia notifica ripristino

### Aggiornamento Zero-Downtime

```bash
# Aggiorna applicazione senza interruzioni
./scripts/update.sh
```

Operazioni:
- Crea backup pre-aggiornamento
- Abilita modalità manutenzione
- Aggiorna codice da repository
- Installa nuove dipendenze
- Esegue migrazioni database
- Pulisce e ricarica cache
- Riavvia servizio queue
- Disabilita modalità manutenzione
- Invia notifica aggiornamento

### Monitoraggio Sistema

```bash
# Monitora salute sistema e servizi
./scripts/monitor.sh
```

Monitora:
- Utilizzo CPU (>80% = avviso)
- Utilizzo memoria (>80% = avviso)
- Utilizzo disco (>90% = avviso)
- Stato servizi (nginx, queue, redis, postgresql)
- Salute applicazione (risposta HTTP)
- Connessione database
- Connessione Redis
- Job queue falliti
- Invia avvisi email per problemi rilevati

### Automazione

#### Cron Jobs

Configura questi script in cron per automazione:

```bash
# Controlli ogni ora
0 * * * * /var/www/next-gold/scripts/checks.sh

# Backup giornaliero alle 2:00
0 2 * * * /var/www/next-gold/scripts/backup.sh

# Monitoraggio ogni 15 minuti
*/15 * * * * /var/www/next-gold/scripts/monitor.sh
```

#### Log degli Script

Tutti gli script registrano attività in:
- `/var/log/next-gold/monitor.log`
- `/var/log/next-gold/backup.log`
- `/var/log/next-gold/restore.log`

## Manutenzione

### Pulizia Log

```bash
# Rota log applicazione
sudo -u www-data php /var/www/next-gold/artisan log:clear

# Pulizia vecchi backup
find /var/www/next-gold/storage/app/backups -name "*.json" -mtime +30 -delete
```

### Ottimizzazione Database

```bash
# Ottimizza tabelle PostgreSQL
sudo -u postgres vacuumdb --analyze next_gold

# Ricostruisci indici
sudo -u postgres reindexdb next_gold
```

### Aggiornamento Applicazione

```bash
cd /var/www/next-gold

# Backup prima dell'aggiornamento
php artisan backup:export

# Pull aggiornamenti
sudo git pull origin main

# Installa nuove dipendenze
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci && sudo -u www-data npm run build

# Esegui migrazioni
sudo -u www-data php artisan migrate --force

# Ricarica servizi
sudo systemctl reload nginx
sudo systemctl restart next-gold-queue
```

## Troubleshooting

### Problemi Comuni

#### 1. Prezzo Oro Non Si Aggiorna

```bash
# Verifica configurazione API
php artisan tinker
config('gold.api_url')
config('gold.api_key')

# Test connessione manuale
php artisan gold:fetch

# Controlla log
tail -f storage/logs/laravel.log | grep gold
```

#### 2. Queue Jobs Bloccati

```bash
# Verifica processi queue
ps aux | grep queue:work

# Riavvia servizio queue
sudo systemctl restart next-gold-queue

# Controlla failed jobs
php artisan queue:failed
```

#### 3. Errore Database Connection

```bash
# Test connessione DB
php artisan tinker
DB::connection()->getPdo()

# Verifica credenziali PostgreSQL
sudo -u postgres psql -c "SELECT version();"

# Controlla configurazione .env
cat .env | grep DB_
```

#### 4. Problemi Permessi File

```bash
# Correggi permessi storage
sudo chown -R www-data:www-data /var/www/next-gold/storage
sudo chmod -R 755 /var/www/next-gold/storage

# Link storage se mancante
sudo -u www-data php artisan storage:link
```

### Log Files

- **Applicazione**: `/var/www/next-gold/storage/logs/laravel.log`
- **Nginx**: `/var/log/nginx/next-gold_error.log`
- **PostgreSQL**: `/var/log/postgresql/postgresql-16-main.log`
- **Redis**: `/var/log/redis/redis-server.log`
- **Queue**: Controlla con `php artisan queue:failed`

## Recovery

### Restore da Backup

```bash
# Importa backup applicazione
sudo -u www-data php /var/www/next-gold/artisan backup:import /path/to/backup.json --mode=replace

# Oppure restore PostgreSQL
sudo -u postgres psql next_gold < backup.sql
```

### Reset Setup Wizard

Se il setup wizard rimane attivo dopo la creazione dell'admin:

```bash
# Forza creazione admin via artisan
php artisan tinker
$user = new App\Models\User;
$user->fill([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'username' => 'admin',
    'email' => 'admin@nextgold.com',
    'password' => Hash::make('password')
]);
$user->save();
$user->assignRole('admin');
```

## Performance

### Ottimizzazioni

```bash
# Cache configurazione
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ottimizza autoloader
composer install --optimize-autoloader

# Precarica OPcache
php -r "opcache_reset();"
```

### Monitoraggio Query Lente

```sql
-- Query lente PostgreSQL
SELECT query, calls, total_time, mean_time, rows
FROM pg_stat_statements
ORDER BY mean_time DESC
LIMIT 10;
```

## Sicurezza

### Aggiornamenti di Sicurezza

```bash
# Aggiorna sistema
sudo apt update && sudo apt upgrade -y

# Aggiorna PHP e estensioni
sudo apt install php8.4 php8.4-* --only-upgrade

# Rinnova certificati SSL
sudo certbot renew
```

### Audit Sicurezza

```bash
# Verifica permessi file
find /var/www/next-gold -type f -perm 777
find /var/www/next-gold -type d -perm 777

# Controlla utenti inattivi
php artisan tinker
App\Models\LoginLog::where('created_at', '<', now()->subDays(90))->count()
```

## Contatti di Emergenza

- **Sviluppatore**: [Contatto sviluppatore]
- **Hosting Provider**: [Provider supporto]
- **Documentazione**: https://github.com/redshapeagency/next-gold

## Checklist Manutenzione Mensile

- [ ] Verifica aggiornamenti sicurezza
- [ ] Backup completo sistema
- [ ] Test funzionalità critiche
- [ ] Pulizia log vecchi
- [ ] Ottimizzazione database
- [ ] Verifica spazio disco
- [ ] Test procedure recovery
