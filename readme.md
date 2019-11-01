# Installation

Install all the dependencies:

    composer install

Create an .env file and copy all the contents from .env.example in the root directory of the project:

    vim ~/PROJECT_PATH/.env

Note: 
Ensure that you specify:

*APP_KEY*
- You must specify a 32 character long string for this variable

*DB_HOST*, *DB_PORT*, *DB_DATABASE*, *DB_USERNAME*, *DB_PASSWORD*
- You must specify a valid database credentials for persistence

Generate token for Json Web Token Authentication:

    php artisan jwt:secret

Migrate all the database table:

    php artisan migrate

Now its time to run your application locally

    php -S localhost:8800 -t public


# Endpoints

Authentication Endpoint

| ACTION |ENDPOINT  | DESCRIPTION |PARAMETERS  |RESPONSE CODES  |
|--|--|--|--|--|
| POST | /register | Create a new account | name: string <br>email: email<br>password: string<br>password_confirmation: same input as password field | 422: Input Validation Error<br>409: Unexpected Error<br>201: Success |
| POST | /login | Login a registered account | email: email<br>password: string | 401: Unauthorized<br>409: Unexpected Error<br>200: Success |
