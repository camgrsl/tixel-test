## Stack Overview
### Backend
- Laravel 11x
- Redis (for queue messages)
- PostgreSQL (as principal datastore)
- Laravel Sail for the dev env

The code is isolated under src/Order bounded context

### Frontend
- NuxtJS 3

## Run the stack

**Backend**
```
cd pos
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan orders:generate
./vendor/bin/sail artisan queue:work
```

To monitor webhook decoded payload : `docker logs -f pos-laravel.test-1`

**Frontend (NuxtJS)**

```
cd nuxt-ui-app
yarn install
yarn dev
```

http://localhost:3000

### Communication between the PoS and the website

The PoS will automatically send a signed webhook to the website endpoint (example given in the pos app as I have not coded any website) using RSA public/private key pair.

**Signing**: Sender’s Private Key → Verified by Recipient using Sender’s Public Key.

**Encryption**: Recipient’s Public Key → Decrypted by Recipient using Recipient’s Private Key.

All key are stored inside `storage/keys` folder of the app as a demo purpose. 
In a production env, they would be encrypted with a secret manager or using laravel encryption.

Decoded webhook payload available on the stdout of the docker container.

### Areas of improvement

- Authentication system
- More unit / feature tests
- Better handling of unsuccessful webhook (500) (retry policy / lock)
- Segment eloquent model with a domain layer 
- Better the order system (order items with pizza names & SKU / transition history for better tracking)