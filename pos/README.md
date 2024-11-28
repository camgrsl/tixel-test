## Stack Overview
### Backend
- Laravel 11x
- Redis (for queue messages)
- PostgreSQL (as principal datastore)
- Laravel Sail for the dev env

### Frontend
- NuxtJS 3

## Run the stack
**Backend**
```
cp .env.example .env
composer install
./vendor/bin/sail up -D
./vendor/bin/sail artisan migrate
./vendor/bim/sail artisan orders:generate
./vendor/bin/sail artisan queue:work
```

**Frontend (NuxtJS)**

```
yarn install
yarn run serve
```

http://localhost:3000

### Communication between the PoS and the website

The PoS will automatically send a signed webhook to the website endpoint (example given in the pos app) using RSA public/private key pair.

**Signing**: Sender’s Private Key → Verified by Recipient using Sender’s Public Key.

**Encryption**: Recipient’s Public Key → Decrypted by Recipient using Recipient’s Private Key.

All key are stored inside `storage/keys` folder of the app. 

Decoded webhook payload available on the stdout of the docker container.

### Areas of improvement

- Authentication system
- More unit / feature test
- Better handling of unsuccessful webhook (500) (retry policy / lock)
- Segment eloquent model with a domain layer 
- Better the order system (order items / transition history for better tracking)
