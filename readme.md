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

**Authentication Endpoint**

| ACTION |ENDPOINT  | DESCRIPTION |PARAMETERS  |RESPONSE CODES  | DATA |
|--|--|--|--|--|--|
| POST | /api/register | Create a new account | name: string <br>email: email<br>password: string<br>password_confirmation: same input as password field | 422: Input Validation Error<br>409: Unexpected Error<br>201: Success | `{"data": {"id": 1,"name": "Jay Doe","email": "jay@doe.com","created_at": "2019-11-01 07:12:09","updated_at": "2019-11-01 07:12:09"}}` |
| POST | /api/login | Login a registered account | email: email<br>password: string | 401: Unauthorized<br>409: Unexpected Error<br>200: Success | `{"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODgwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTU3MjU5MjQ3MCwiZXhwIjoxNTcyNTk2MDcwLCJuYmYiOjE1NzI1OTI0NzAsImp0aSI6InpvOXdSbVQwWVhhYm80dHoiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.R-P06j3Sw_a-VW_LshaDr5xVZp6M1iViVkMPfb7snLk","token_type": "bearer","expires_in": 3600}` |

**User Endpoint**

| ACTION |ENDPOINT  | DESCRIPTION |PARAMETERS  |RESPONSE CODES  | DATA |
|--|--|--|--|--|--|
| GET | /api/user | Get all the Users | limit: integer, optional<br>page: integer, optional | 422: Input Validation Error<br>409: Unexpected Error<br>200: Success | `{"data":[{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}]}` |
| PATCH  | /api/user/{id} | Update an User | name: string, optional | 422: Input Validation Error<br>409: Unexpected Error<br>200: Success | `{"data":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}}` |

**Robot Endpoint**

| ACTION |ENDPOINT  | DESCRIPTION |PARAMETERS  |RESPONSE CODES  | . |
|--|--|--|--|--|--|
| GET | /api/robot | Get all the robots | limit: integer, optional<br>page: integer, optional | 422: Input Validation Error<br> 409: Unexpected Error<br>200: Success | `{"data":[{"id":1,"name":"Terminal","weight":"90.32","power":"2.00","speed":"3.00","created_at":"2019-11-01 07:26:04","updated_at":"2019-11-01 07:26:04","user":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}},{"id":2,"name":"Postman","weight":"121.12","power":"12.00","speed":"2.00","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15","user":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}},{"id":3,"name":"Paw","weight":"39.00","power":"2.00","speed":"2.00","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15","user":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}},{"id":4,"name":"Slack","weight":"122.00","power":"3.00","speed":"2.00","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15","user":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}}]}` |
| GET | /api/robot/{id} | Show a robot |  | 404: Robot not found<br>409: Unexpected Error<br>200: Success | `{"data":{"id":2,"name":"Postman","weight":"121.12","power":"12.00","speed":"2.00","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15","user":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}}}` |
| POST | /api/robot | Create a robot | name: string<br>weight: string<br>power: numeric<br>speed: numeric | 422: Input Validation Error<br>409: Unexpected Error<br>201: Success | `{"data":{"id":1,"name":"Terminal","weight":"90.32","power":"2","speed":"3","created_at":"2019-11-01 07:26:04","updated_at":"2019-11-01 07:26:04"}}` |
| POST | /api/robot/uploadSpreadsheet | Create robots via spreadsheet(csv) upload | robot_spreadsheet: file, should be .csv format | 422: Input Validation Error<br>409: Unexpected Error<br>201: Success | `{"data":[{"id":2,"name":"Postman","weight":"121.12","power":"12","speed":"2","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15"},{"id":3,"name":"Paw","weight":"39","power":"2","speed":"2","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15"},{"id":4,"name":"Slack","weight":"122","power":"3","speed":"2","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:31:15"}]}` |
| PATCH | /api/robot/{id} | Update a robot | weight: numeric, optional<br>speed: numeric, optional<br>power: numeric, optional | 404: Robot not found<br>422: Input Validation Error<br>409: Unexpected Error<br>200: Success | `{"data":{"id":2,"name":"Postman","weight":"4.00","power":"5.00","speed":"6.00","created_at":"2019-11-01 07:31:15","updated_at":"2019-11-01 07:37:00","user":{"id":1,"name":"Jay Doe","email":"jay@doe.com","created_at":"2019-11-01 07:12:09","updated_at":"2019-11-01 07:12:09"}}}` |
| DELETE | /api/robot/{id} | Delete a robot |  | 404: Robot not found<br>409: Unexpected Error<br>204: Success with no content |  |

**Battle Endpoint**

| ACTION |ENDPOINT  | DESCRIPTION |PARAMETERS  |RESPONSE CODES  | . |
|--|--|--|--|--|--|
| POST | /api/battle/fight | Fight a robot | location: string<br>robot_id: integer<br>opponent_robot_id: integer | 422: Input Validation Error<br>409: Unexpected Error<br>200: Success | `{"data":{"id":1,"location":"Hiroshima","created_at":"2019-11-01 07:44:55","updated_at":"2019-11-01 07:44:55","challengers":[{"id":1,"robot_id":1,"user_id":1,"battle_id":1,"is_victorious":1,"is_initiator":1,"created_at":"2019-11-01 07:44:55","updated_at":""},{"id":2,"robot_id":2,"user_id":1,"battle_id":1,"is_victorious":0,"is_initiator":0,"created_at":"2019-11-01 07:44:55","updated_at":""}]}}` |
| GET | /api/battle/results | Get the battle results | limit: integer, optional<br>page: integer, optional | 422: Input Validation Error<br> 409: Unexpected Error<br> 200: Success | `{"data":[{"id":3,"location":"Kyoto","winning_robot":{"id":4,"name":"Slack","weight":"122.00","power":"3.00","speed":"2.00"},"defeated_robot":{"id":1,"name":"Terminal","weight":"90.32","power":"2.00","speed":"3.00"},"created_at":"2019-11-01 07:48:42","updated_at":"2019-11-01 07:48:42"},{"id":2,"location":"Hiroshima","winning_robot":{"id":1,"name":"Terminal","weight":"90.32","power":"2.00","speed":"3.00"},"defeated_robot":{"id":3,"name":"Paw","weight":"39.00","power":"2.00","speed":"2.00"},"created_at":"2019-11-01 07:48:31","updated_at":"2019-11-01 07:48:31"},{"id":1,"location":"Hiroshima","winning_robot":{"id":1,"name":"Terminal","weight":"90.32","power":"2.00","speed":"3.00"},"defeated_robot":{"id":2,"name":"Postman","weight":"4.00","power":"5.00","speed":"6.00"},"created_at":"2019-11-01 07:44:55","updated_at":"2019-11-01 07:44:55"}]}` |
| GET | /api/battle/leaderboard | Get the robot ranking | limit: integer, optional<br>page: integer, optional | 422: Input Validation Error<br>409: Unexpected Error<br>200: Success | `{"data":[{"robot_id":1,"name":"Terminal","battle_count":3,"winning_count":2,"losing_count":1},{"robot_id":4,"name":"Slack","battle_count":1,"winning_count":1,"losing_count":0},{"robot_id":2,"name":"Postman","battle_count":1,"winning_count":0,"losing_count":1},{"robot_id":3,"name":"Paw","battle_count":1,"winning_count":0,"losing_count":1}]}` |
