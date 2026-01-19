# API Documentation

## Authentication

All API endpoints require authentication. The application uses Laravel's built-in authentication system with Laravel Breeze.

### Obtaining Authentication Token

For web-based access, use the login form to authenticate. For API access, you would typically use Laravel Sanctum or Passport for token-based authentication.

## Application Management Endpoints

### GET /applications

Retrieve a list of all applications.

**Response:**
```json
{
    "applications": [
        {
            "name": "application-name",
            "path": "/var/www/html/applications/application-name",
            "created_at": 1678886400,
            "laravel_version": "^9.0",
            "php_version": "^8.0",
            "port": 8000,
            "is_running": true,
            "url": "http://server.poudelbijaya.com.np:8000"
        }
    ]
}
```

### POST /applications

Create a new application.

**Parameters:**
- `name` (string, required): The name of the application
- `version` (string, required): PHP version (8.0, 8.1, 8.2, 8.3, 8.4)
- `repo_url` (string, optional): Git repository URL
- `access_token` (string, optional): Personal access token for private repositories

**Response:**
```json
{
    "success": true,
    "message": "Application created successfully",
    "redirect_to": "/applications/{name}/setup"
}
```

### DELETE /applications/{name}

Delete an application.

**Parameters:**
- `name` (string, required): The name of the application to delete

**Response:**
```json
{
    "success": true,
    "message": "Application deleted successfully"
}
```

### POST /applications/{name}/deploy

Deploy an application to the web root.

**Parameters:**
- `name` (string, required): The name of the application to deploy

**Response:**
```json
{
    "success": true,
    "message": "Application deployed successfully"
}
```

### GET /applications/{name}/setup

Get the setup page for an application.

**Response:**
- HTML page with setup form

### POST /applications/{name}/setup

Store setup configuration for an application.

**Parameters:**
- `environment` (string, optional): Environment configuration
- `generate_key` (boolean, optional): Whether to generate application key
- `php_version` (string, required): PHP version
- `run_commands` (array, optional): Array of commands to run (composer, npm, migrate, seed)

**Response:**
```json
{
    "success": true,
    "message": "Application setup completed successfully"
}
```

### GET /applications/{name}/nginx-config

Get nginx configuration for an application.

**Response:**
```json
{
    "name": "application-name",
    "config": "server {\n    listen 80;\n    server_name application-name.local;\n    # ... rest of config\n}",
    "path": "/var/www/html/applications/application-name",
    "php_version": "8.1"
}
```

## Site Management Endpoints

### GET /sites

Retrieve a list of all sites with port assignments.

**Response:**
```json
{
    "sites": [
        {
            "name": "application-name",
            "path": "/var/www/html/applications/application-name",
            "created_at": 1678886400,
            "laravel_version": "^9.0",
            "php_version": "^8.0",
            "is_running": true,
            "port": 8000,
            "url": "http://server.poudelbijaya.com.np:8000"
        }
    ]
}
```

### POST /sites/{name}/start

Start an application on its assigned port.

**Parameters:**
- `name` (string, required): The name of the application to start

**Response:**
```json
{
    "success": true,
    "message": "Application started successfully on port 8000"
}
```

### POST /sites/{name}/stop

Stop an application running on its assigned port.

**Parameters:**
- `name` (string, required): The name of the application to stop

**Response:**
```json
{
    "success": true,
    "message": "Application stopped successfully"
}
```

### POST /sites/{name}/update

Update an application from its Git repository.

**Parameters:**
- `name` (string, required): The name of the application to update

**Response:**
```json
{
    "success": true,
    "message": "Application updated successfully"
}
```

### POST /sites/{name}/refresh

Refresh an application by clearing caches.

**Parameters:**
- `name` (string, required): The name of the application to refresh

**Response:**
```json
{
    "success": true,
    "message": "Application refreshed successfully"
}
```

### POST /sites/{name}/nginx-config

Create nginx configuration for an application.

**Parameters:**
- `name` (string, required): The name of the application

**Response:**
```json
{
    "success": true,
    "message": "Nginx configuration created successfully"
}
```

### DELETE /sites/{name}

Remove nginx configuration for an application (does not delete the application).

**Parameters:**
- `name` (string, required): The name of the application

**Response:**
```json
{
    "success": true,
    "message": "Nginx configuration removed successfully"
}
```

## Test Dashboard Endpoints

### GET /test-dashboard

Display the test dashboard with available tests.

**Response:**
- HTML page with test dashboard interface

### POST /test-dashboard/run/{testName}

Run a specific test.

**Parameters:**
- `testName` (string, required): The name of the test to run

Available test names:
- `repository_cloning`
- `directory_creation`
- `file_verification`
- `sudo_integration`
- `permission_management`
- `deployment_flow`
- `composer_install`
- `application_start`
- `nginx_config_generation`
- `database_migration`
- `cache_clearing`
- `app_key_generation`
- `all_tests`

**Response:**
```json
{
    "test_name": "repository_cloning",
    "status": "completed",
    "timestamp": "2026-01-15T16:30:00.000000Z",
    "output": "Repository cloned successfully...",
    "success": true
}
```

## Deploy Official Laravel Endpoint

### POST /applications/deploy-official-laravel

Deploy the official Laravel application with test migration and seeding.

**Response:**
```json
{
    "success": true,
    "message": "Official Laravel application deployed with test migration and seeding!"
}
```

## Error Responses

All error responses follow this format:

```json
{
    "success": false,
    "message": "Error message describing the issue",
    "error_code": 404
}
```

## HTTP Status Codes

- `200 OK`: Request successful
- `302 Found`: Redirect response (common for authenticated routes)
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Access denied
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server error

## Request Headers

When making API requests, include these headers:

```
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest
```

For authenticated requests, ensure the user is logged in via the web interface, as the API endpoints are protected by Laravel's web authentication middleware.