# VirtuaalinenSovitusHuone.WebAPI

## Yleiskuvaus

Tarjoaa web api:n virtuaalisen sovitushuoneen prototyypille.

## Asennus

```bash
composer install
```

Kopioi `.env.example` tiedostoon `.env` ja konfiguroi tarvittavat tiedot (sovelluksen nimi, url jne, tietokannan tiedot)

Sovellus käyttää oletuksena autentikointiin Auth0:n SDK:ta, joten `.env`-tiedostossa tulee konfiguroida siihen liittyvät tiedot

```env
AUTH0_DOMAIN={YOUR_AUTH0_DOMAIN}
AUTH0_CLIENT_ID={YOUR_AUTH0_CLIENT_ID}
AUTH0_API_IDENTIFIER={YOUR_AUTH0_API_IDENTIFIER}
```

Sovellus hyödyntää oletuksena tiedostontallennusjärjestelmänä Azure Blob Storagea, jossa 2 erillistä containeria (muille asseteille public access, käyttäjän avatareille ei), nämä konfiguroidaan myös `.env`:ssä

```env
FILESYSTEM_DRIVER=azure-assets
...
AZURE_STORAGE_NAME={YOUR_AZURE_STORAGE_ACCOUNT_NAME}
AZURE_STORAGE_KEY={YOUR_AZURE_STORAGE_ACCOUNT_ACCESS_KEY}
AZURE_STORAGE_CONTAINER={YOUR_AZURE_STORAGE_ASSET_CONTAINER_NAME}
AZURE_STORAGE_CONTAINER_FOR_AVATARS={YOUR_AZURE_STORAGE_AVATAR_CONTAINER_NAME}
```

## Testaus

```bash
vendor/bin/phpunit
```
