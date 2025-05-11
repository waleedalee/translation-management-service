# Translation Management Service

A high-performance API-driven service for managing translations across multiple locales with support for tagging, exporting, and secure authentication.

## Features

- **Multi-locale Support**: Store translations for multiple locales (e.g., en, fr, es, de) with easy expansion to new languages
- **Contextual Tagging**: Tag translations for context (e.g., mobile, desktop, web) for better organization
- **Comprehensive API**: Endpoints to create, update, view, and search translations by tags, keys, or content
- **JSON Export**: Efficient endpoint to supply translations
- **Secure Authentication**: API token authentication using Laravel Sanctum
- **High Performance**: Optimized for large datasets (100k+ records) with response times < 200ms
- **Caching System**: Implemented caching for frequently accessed data with automatic cache invalidation

## Technical Requirements

- PHP 8.2+
- Laravel 12.x
- MySQL 8.0+ / MariaDB 10.5+
- Composer

## Installation

1. Clone the repository
   ```bash
   git clone https://github.com/waleedalee/translation-management-service.git
   cd translation-management-service
   ```

2. Install dependencies
   ```bash
   composer install
   ```

3. Set up environment variables
   ```bash
   cp .env.example .env
   # Edit .env file with your database credentials
   ```

4. Generate application key
   ```bash
   php artisan key:generate
   ```

5. Run migrations
   ```bash
   php artisan migrate
   ```

6. Seed the database with initial translations
   ```bash
   php artisan db:seed
   ```

7. Start the development server
   ```bash
   php artisan serve
   ```

## API Authentication

The Translation Management Service uses Laravel Sanctum for API authentication:

1. **Register a new user**
   ```
   POST /api/auth/register
   {
     "name": "User Name",
     "email": "user@example.com",
     "password": "password",
     "password_confirmation": "password"
   }
   ```

2. **Login to get token**
   ```
   POST /api/auth/login
   {
     "email": "user@example.com",
     "password": "password"
   }
   ```

3. **Use token in requests**
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

## API Endpoints

### Translation Endpoints

- `GET /api/translations` - List translations (with filters for key, locale, content, tags)
- `GET /api/translations/{id}` - Get a specific translation
- `POST /api/translations` - Create a new translation
- `PUT /api/translations/{id}` - Update a translation
- `DELETE /api/translations/{id}` - Delete a translation
- `GET /api/translations/export/json` - Export translations as JSON (filterable by locale and tags)
- `POST /api/translations/search` - Search translations by query

### Authentication Endpoints

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and get access token
- `POST /api/auth/logout` - Logout (invalidate token)

## Data Structure

Example translation record:
```json
{
  "id": 1,
  "key": "welcome_message",
  "locale": "en",
  "content": "Welcome!",
  "tags": ["web", "mobile"],
  "created_at": "2023-07-22T14:30:00.000000Z",
  "updated_at": "2023-07-22T14:30:00.000000Z"
}

```


## Postman Colletion

The postman collection is provided and it is set up to automatically capture the authentication token from the Login response. Follow these steps:

1. **Register a User** (only needed once):
   - Open the "Authentication" folder in the collection
   - Select the "Register" request
   - Click "Send" to register a test user
   - The response will contain an access token

2. **Login**:
   - Open the "Login" request
   - Verify email and password match your registered user
   - Click "Send"
   - The token will be automatically saved to the `auth_token` environment variable thanks to the test script

3. **Test Protected Endpoints**:
   - All other requests in the collection are configured to use the `auth_token` variable
   - No need to manually copy tokens between requests

## Testing API Endpoints

### Translations

- **List Translations**: Use query parameters to filter by locale, key, content, or tags
- **Get Translation**: Update the URL with the ID of the translation you want to retrieve
- **Create Translation**: Send a POST request with the translation details
- **Update Translation**: Update an existing translation (modify the ID in the URL)
- **Delete Translation**: Remove a translation (modify the ID in the URL)
- **Export Translations**: Export translations as JSON, filtered by locale and tags
- **Search Translations**: Search for translations with specific criteria

## Performance Testing

To test performance with large datasets, use the provided command:

```bash
# Generate 100,000 test records
php artisan translations:generate

# Or specify a custom number
php artisan translations:generate 50000
```
