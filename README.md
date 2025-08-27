# Next Gold - Gestionale per Compro Oro

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