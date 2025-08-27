# Next Gold - Gestionale per Compro Oro

## 📋 Panoramica

Next Gold è un sistema di gestione completo e moderno per negozi di compro oro e preziosi. Sviluppato con Laravel 11 e PHP 8.4, offre tutte le funzionalità necessarie per gestire efficacemente un'attività di compro oro.

## ✨ Caratteristiche Principali

### 🔐 Sistema di Autenticazione e Autorizzazione
- Login sicuro con protezione CSRF
- Sistema di ruoli e permessi granulari
- Audit trail completo delle attività

### 📊 Dashboard Centralizzata
- Panoramica delle metriche di business in tempo reale
- Grafici interattivi per analisi delle performance
- Alerts e notifiche importanti

### 👥 Gestione Clienti
- Anagrafica completa dei clienti
- Storico transazioni per cliente
- Documenti e contratti associati

### 📦 Gestione Inventario
- Catalogo completo degli articoli
- Tracking delle giacenze in tempo reale
- Categorizzazione avanzata dei prodotti

### 📄 Gestione Documenti
- Generazione automatica di ricevute e contratti
- Archiviazione digitale sicura
- Sistema di numerazione progressiva

### 🗄️ Archivio Digitale
- Conservazione a lungo termine dei documenti
- Ricerca avanzata e filtri
- Backup automatico

### ⚙️ Impostazioni Avanzate
- Configurazione personalizzabile dell'applicazione
- Gestione utenti e permessi
- Impostazioni di backup e sicurezza

### 💰 Prezzi Oro in Tempo Reale
- Integrazione con API di quotazioni internazionali
- Aggiornamento automatico dei prezzi
- Storico delle quotazioni

## 🛠️ Requisiti di Sistema

### Server Requirements
- **OS**: Ubuntu 20.04+ (testato su Ubuntu 24.04)
- **PHP**: 8.4+
- **Database**: PostgreSQL 16+
- **Cache**: Redis 7+
- **Web Server**: Nginx
- **Memory**: Minimo 2GB RAM
- **Storage**: Minimo 10GB spazio libero

### PHP Extensions Required
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- PDO_PGSQL
- Tokenizer
- XML
- GD
- Redis
- Zip

## 🚀 Installazione Automatica

### Installazione Rapida (Ubuntu 24.04)

```bash
# 1. Clona il repository
git clone https://github.com/redshapeagency/next-gold.git
cd next-gold

# 2. Rendi eseguibile lo script di installazione
chmod +x install.sh

# 3. Esegui l'installazione automatica
sudo ./install.sh
```

Lo script di installazione automatica:
- ✅ Installa tutti i prerequisiti (PHP 8.4, PostgreSQL, Redis, Nginx)
- ✅ Configura il database e le credenziali
- ✅ Installa le dipendenze PHP e Node.js
- ✅ Configura l'ambiente di produzione
- ✅ Imposta i permessi corretti
- ✅ Configura Nginx con SSL (opzionale)
- ✅ Crea i servizi systemd per queue e scheduler

### Configurazione Personalizzata

Prima di eseguire l'installazione, puoi personalizzare le variabili nel file `install.sh`:

```bash
# Modifica le configurazioni in install.sh
APP_NAME="next-gold"
APP_DOMAIN="your-domain.com"
DB_NAME="next_gold"
DB_USER="next_gold_user"
# ... altre configurazioni
```

## 📖 Uso dell'Applicazione

### Primo Accesso

Dopo l'installazione, accedi all'applicazione:

1. **URL**: `https://your-domain.com` (o l'URL configurato)
2. **Credenziali default**:
   - Email: `admin@nextgold.local`
   - Password: `password123`

⚠️ **IMPORTANTE**: Cambia immediatamente la password dopo il primo accesso!

### Configurazione Iniziale

1. **Accedi al pannello Impostazioni**
2. **Configura i dati dell'azienda**
3. **Imposta le API per i prezzi dell'oro** (se disponibili)
4. **Crea utenti aggiuntivi** con i ruoli appropriati
5. **Configura i backup automatici**

### Gestione Quotidiana

#### Dashboard
- Monitora le metriche principali
- Visualizza le transazioni recenti
- Controlla gli alert di sistema

#### Gestione Clienti
- Aggiungi nuovi clienti dalla sezione "Clienti"
- Registra documenti di identità
- Tieni traccia dello storico transazioni

#### Gestione Inventario
- Cataloga i prodotti ricevuti
- Aggiorna le quantità in tempo reale
- Organizza per categorie

#### Documenti e Archivio
- Genera automaticamente ricevute
- Archivia i documenti legali
- Utilizza la ricerca avanzata per trovare documenti storici

## 🔧 Manutenzione

### Backup Automatico

Il sistema include backup automatici configurabili:

```bash
# Backup manuale
php artisan backup:run

# Verifica stato backup
php artisan backup:list
```

### Log Monitoring

```bash
# Visualizza log applicazione
tail -f /var/www/next-gold/storage/logs/laravel.log

# Log Nginx
sudo tail -f /var/log/nginx/error.log
```

### Aggiornamenti

```bash
# Entra nella directory dell'applicazione
cd /var/www/next-gold

# Aggiorna le dipendenze
composer update --no-dev
npm update && npm run build

# Aggiorna cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🛡️ Sicurezza

### Caratteristiche di Sicurezza Implementate

- **HTTPS Forzato** con certificati SSL automatici
- **Protezione CSRF** su tutti i form
- **Validazione Input** rigorosa
- **Audit Logging** completo
- **Rate Limiting** sulle API
- **Protezione XSS** e SQL Injection
- **Backup Crittografati**

### Best Practices

1. **Cambia le credenziali default**
2. **Mantieni il software aggiornato**
3. **Monitora i log regolarmente**
4. **Esegui backup periodici**
5. **Usa password forti**
6. **Limita l'accesso SSH**

## 🔍 Risoluzione Problemi

### Problemi Comuni

#### 500 Internal Server Error
```bash
# Controlla i permessi
sudo chown -R www-data:www-data /var/www/next-gold
sudo chmod -R 755 /var/www/next-gold
sudo chmod -R 775 /var/www/next-gold/storage

# Controlla i log
tail -f /var/www/next-gold/storage/logs/laravel.log
```

#### Database Connection Error
```bash
# Verifica servizio PostgreSQL
sudo systemctl status postgresql

# Testa connessione database
sudo -u postgres psql -c "SELECT 1;"
```

#### Redis Connection Error
```bash
# Verifica servizio Redis
sudo systemctl status redis-server

# Testa connessione Redis
redis-cli ping
```

### Support

Per supporto tecnico o segnalazione bug:
- **Repository**: [https://github.com/redshapeagency/next-gold](https://github.com/redshapeagency/next-gold)
- **Issues**: [https://github.com/redshapeagency/next-gold/issues](https://github.com/redshapeagency/next-gold/issues)

## 📄 Licenza

Questo progetto è rilasciato sotto licenza MIT. Vedi il file `LICENSE` per maggiori dettagli.

## 🏢 Crediti

Sviluppato da **Red Shape Agency** - Soluzioni digitali innovative per il business.

---

**Next Gold** - La soluzione completa per la gestione del tuo compro oro. 🥇 - Gestionale per Compro Oro

Next Gold è una webapp completa per la gestione di un negozio compro oro, sviluppata con Laravel 11, PostgreSQL, Redis e TailwindCSS.

## Caratteristiche Principali

- 🏪 **Gestione completa del negozio**: Clienti, Magazzino, Documenti (Acquisti/Vendite)
- 💰 **Monitoraggio prezzi oro in tempo reale** con API configurabili
- 📄 **Generazione PDF** per documenti di acquisto e vendita
- 👥 **Sistema ruoli e permessi** (Admin, Operator, Viewer)
- 🔐 **Autenticazione sicura** con login via email o username
- 📊 **Dashboard** con KPI e grafici di entrate/uscite
- 🗄️ **Archivio prodotti** venduti con possibilità di ripristino
- ⚙️ **Configurazione avanzata** e backup/ripristino dati
- 📱 **Interfaccia responsive** moderna e intuitiva

## Stack Tecnologico

- **Backend**: PHP 8.4, Laravel 11
- **Database**: PostgreSQL 16
- **Cache/Queue**: Redis 7
- **Frontend**: Blade Templates, TailwindCSS, Alpine.js
- **Build Tools**: Vite, Node.js 20
- **Web Server**: Nginx
- **SSL**: Certbot (opzionale)

## Requisiti di Sistema

### Sviluppo Locale
- PHP 8.4+ con estensioni: pgsql, redis, mbstring, intl, gd, bcmath, xml, curl, dom, zip, fileinfo
- Composer 2.x
- Node.js 20+
- PostgreSQL 16+
- Redis 7+

### Produzione (Ubuntu 24.04)
- Tutti i requisiti di sviluppo
- Nginx
- Certbot (per SSL)
- Supervisor (per code)

## Installazione

### Quick Start (Sviluppo Locale)

1. **Clone e dipendenze**:
```bash
git clone <repository-url>
cd next-gold
composer install
npm ci
```

2. **Configurazione**:
```bash
cp .env.example .env
php artisan key:generate
# Configura DB e Redis in .env
```

3. **Database e avvio**:
```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Visita http://localhost:8000 e segui il setup wizard per creare il primo utente admin.

### Installazione Produzione

Per Ubuntu 24.04, esegui lo script automatico:

```bash
sudo chmod +x install.sh
sudo ./install.sh
```

Lo script è **idempotente** e gestisce:
- Installazione automatica dipendenze (PHP, PostgreSQL, Redis, Nginx, Node.js)
- Configurazione database e utenti
- Setup Nginx con SSL opzionale via Certbot
- Configurazione queue worker con systemd
- Scheduler Laravel via cron
- Permessi e sicurezza

## Configurazione

### Variabili Ambiente Principali

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=next_gold
DB_USERNAME=next_gold_user
DB_PASSWORD=your_secure_password

# Cache/Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# API Prezzo Oro
GOLD_PROVIDER=custom
GOLD_API_URL=https://api.example.com/v1/gold
GOLD_API_KEY=your_api_key
GOLD_UNIT=oz
GOLD_CURRENCY=EUR
GOLD_FETCH_INTERVAL=60

# Backup
BACKUP_HMAC_SECRET=your_secret_key

# Rate Limiting
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_MINUTES=10
```

### Setup Wizard

Al primo accesso (quando non esistono utenti), viene mostrato il setup wizard per:
- Creare il primo utente amministratore
- Configurare i dati base del negozio
- Impostare le API per il prezzo dell'oro

## Utilizzo

### Moduli Principali

1. **Dashboard**: KPI, grafici entrate/uscite, prezzo oro corrente
2. **Clienti**: Anagrafica completa con documenti di identità
3. **Magazzino**: Gestione prodotti con foto, categorie, prezzi
4. **Documenti**: Wizard per acquisti/vendite con PDF
5. **Archivio**: Prodotti venduti con ripristino
6. **Impostazioni**: Configurazione negozio, utenti, API, backup

### Flussi di Lavoro

**Acquisto da Cliente**:
1. Crea/seleziona cliente
2. Crea documento acquisto
3. Aggiungi prodotti al documento  
4. Conferma → prodotti creati in magazzino

**Vendita a Cliente**:
1. Crea/seleziona cliente
2. Crea documento vendita
3. Seleziona prodotti dal magazzino
4. Conferma → prodotti archiviati

**Monitoraggio Oro**:
- Prezzo aggiornato automaticamente ogni minuto
- Configurazione provider API nelle impostazioni
- Test connessione disponibile

## API e Integrazioni

### Driver Prezzo Oro

Supporta multiple provider configurabili:
- **Custom**: API generica configurabile
- **Metals API**: Provider specifico con conversioni automatiche

### Backup/Ripristino

- **Export**: JSON firmato con HMAC per sicurezza
- **Import**: Validazione firma + anteprima modifiche
- **Opzioni**: Append/Replace per dati master

## Sicurezza

- Rate limiting sui login
- Validazione CSRF su tutti i form
- Sanitizzazione input e validazioni robuste
- Headers di sicurezza Nginx
- Log completi di accessi e azioni
- Backup firmati crittograficamente

## Sviluppo

### Comandi Utili

```bash
# Sviluppo
php artisan serve
npm run dev

# Tests
php artisan test
vendor/bin/phpstan analyse
vendor/bin/pint --test

# Gold price
php artisan gold:fetch

# Queue
php artisan queue:work
```

### Struttura Progetto

```
app/
├── Console/Commands/          # Comandi Artisan
├── Http/
│   ├── Controllers/          # Controller principali
│   ├── Requests/            # Form validation
│   └── Middleware/          # Middleware custom
├── Models/                  # Modelli Eloquent
├── Observers/               # Observer per audit log
├── Policies/               # Policy autorizzazioni
└── Services/               # Business logic
    ├── GoldPrice/          # Servizio prezzi oro
    ├── DocumentNumberService.php
    └── BackupService.php

resources/
├── views/                  # Blade templates
├── css/app.css            # Stili TailwindCSS
└── js/app.js             # JavaScript/Alpine

database/
├── migrations/            # Migrazioni database
└── seeders/              # Seeder dati iniziali

scripts/
├── nginx/                # Template configurazione
├── systemd/             # Servizi sistema
└── checks.sh           # Script verifica

tests/
├── Feature/             # Test funzionali
└── Unit/               # Test unitari
```

## Troubleshooting

### Problemi Comuni

**Queue non funzionano**:
```bash
php artisan queue:restart
sudo systemctl restart next-gold-queue
```

**Prezzo oro non aggiornato**:
```bash
php artisan gold:fetch
# Verifica configurazione API in settings
```

**Permessi file**:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Log e Debug**:
```bash
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

## Contributori

Progetto sviluppato da [Red Shape Agency](https://redshape.it) per la gestione completa di negozi compro oro.

## Licenza

Questo progetto è rilasciato sotto licenza MIT. Vedi il file LICENSE per i dettagli.

---

Per supporto tecnico o personalizzazioni, contatta il team di sviluppo.