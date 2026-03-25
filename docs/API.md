# MCBANKS Laravel API Documentation

## Overview

This document provides comprehensive API documentation for the MCBANKS Laravel application. The API follows RESTful conventions and includes endpoints for authentication, user management, profiles, and geographical data.

## Base URL

```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

## Authentication

### Authentication Methods

The API supports multiple authentication methods:

1. **Session-based authentication** (for web applications)
2. **Token-based authentication** (for API clients)
3. **OAuth integration** (for third-party applications)

### Authentication Headers

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## API Endpoints

### Health Check

#### GET /health

Check API health status and version information.

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2026-03-25T19:19:00.000000Z",
  "version": "1.0.3",
  "environment": "production"
}
```

---

### Authentication Endpoints

#### POST /auth/login

Authenticate user with email and password.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```

**Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "email_verified_at": "2026-03-25T19:19:00.000000Z",
    "roles": ["user"],
    "avatar_url": "https://example.com/storage/avatars/user1.jpg",
    "thumbnail_url": "https://example.com/storage/avatars/thumbnails/user1.jpg"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 3600
}
```

**Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

#### POST /auth/logout

Logout authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

#### POST /auth/register

Register a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "user"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "user@example.com",
    "email_verified_at": null,
    "roles": ["user"]
  },
  "verification_required": true
}
```

#### POST /auth/refresh

Refresh authentication token.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "expires_in": 3600
}
```

---

### User Profile Endpoints

#### GET /user/profile

Get current user's profile information.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "avatar_url": "https://example.com/storage/avatars/user1.jpg",
    "thumbnail_url": "https://example.com/storage/avatars/thumbnails/user1.jpg",
    "profile": {
      "bio": "Software developer passionate about Laravel",
      "phone": "+1234567890",
      "website": "https://johndoe.dev",
      "location": "Nairobi, Kenya",
      "completion_percentage": 85,
      "privacy_settings": {
        "show_email": false,
        "show_phone": true,
        "show_location": true,
        "profile_public": true
      }
    },
    "social_accounts": [
      {
        "provider": "google",
        "nickname": "johndoe",
        "avatar": "https://lh3.googleusercontent.com/...",
        "connected_at": "2026-03-25T19:19:00.000000Z"
      }
    ]
  }
}
```

#### PUT /user/profile

Update current user's profile.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "John Smith",
  "profile": {
    "bio": "Senior Laravel Developer",
    "phone": "+1234567890",
    "website": "https://johnsmith.dev",
    "country_id": 114,
    "state_id": 4701,
    "city_id": 1847,
    "address": "123 Main St",
    "privacy_settings": {
      "show_email": false,
      "show_phone": true,
      "show_location": true,
      "profile_public": true
    }
  }
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "John Smith",
    "profile": {
      "bio": "Senior Laravel Developer",
      "phone": "+1234567890",
      "website": "https://johnsmith.dev",
      "completion_percentage": 90
    }
  }
}
```

#### POST /user/avatar

Upload user avatar image.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** `multipart/form-data`
- `avatar`: Image file (JPG, PNG, GIF, WebP, max 5MB)

**Response (200):**
```json
{
  "success": true,
  "message": "Avatar uploaded successfully",
  "avatar": {
    "url": "https://example.com/storage/avatars/user1.jpg",
    "thumbnail_url": "https://example.com/storage/avatars/thumbnails/user1.jpg",
    "size": 245760,
    "dimensions": {
      "width": 300,
      "height": 300
    }
  }
}
```

#### DELETE /user/avatar

Delete user avatar.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Avatar deleted successfully"
}
```

---

### Public Profile Endpoints

#### GET /users/{id}

Get public user profile by ID.

**Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "avatar_url": "https://example.com/storage/avatars/user1.jpg",
    "thumbnail_url": "https://example.com/storage/avatars/thumbnails/user1.jpg",
    "profile": {
      "bio": "Software developer passionate about Laravel",
      "website": "https://johndoe.dev",
      "location": "Nairobi, Kenya"
    },
    "joined_at": "2026-03-25T19:19:00.000000Z"
  }
}
```

**Response (403):**
```json
{
  "success": false,
  "message": "This profile is private"
}
```

**Response (404):**
```json
{
  "success": false,
  "message": "User not found"
}
```

---

### Geographical Data Endpoints

#### GET /countries

Get list of all countries.

**Query Parameters:**
- `search`: Search countries by name (optional)
- `limit`: Number of results per page (default: 50, max: 200)
- `page`: Page number (default: 1)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 114,
      "name": "Kenya",
      "iso2": "KE",
      "iso3": "KEN",
      "phone_code": "+254",
      "capital": "Nairobi",
      "currency": "KES",
      "currency_symbol": "KSh",
      "flag_emoji": "🇰🇪"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 250,
    "last_page": 5
  }
}
```

#### GET /countries/{id}

Get specific country details.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 114,
    "name": "Kenya",
    "iso2": "KE",
    "iso3": "KEN",
    "phone_code": "+254",
    "capital": "Nairobi",
    "currency": "KES",
    "currency_symbol": "KSh",
    "flag_emoji": "🇰🇪",
    "region": "Africa",
    "subregion": "Eastern Africa",
    "timezones": ["Africa/Nairobi"],
    "coordinates": {
      "latitude": 1.0,
      "longitude": 38.0
    }
  }
}
```

#### GET /countries/{country_id}/states

Get states/provinces for a specific country.

**Query Parameters:**
- `search`: Search states by name (optional)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 4701,
      "name": "Nairobi",
      "country_id": 114,
      "iso_code": "KE-30"
    }
  ]
}
```

#### GET /states/{state_id}/cities

Get cities for a specific state.

**Query Parameters:**
- `search`: Search cities by name (optional)
- `limit`: Number of results per page (default: 50)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1847,
      "name": "Nairobi",
      "state_id": 4701,
      "country_id": 114,
      "coordinates": {
        "latitude": -1.2921,
        "longitude": 36.8219
      }
    }
  ]
}
```

#### GET /kenya/counties

Get all Kenyan counties.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Nairobi",
      "code": "47"
    }
  ]
}
```

#### GET /kenya/counties/{county_id}/constituencies

Get constituencies for a specific Kenyan county.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kamukunji",
      "county_id": 1,
      "type": "constituency"
    }
  ]
}
```

#### GET /kenya/constituencies/{constituency_name}/wards

Get wards for a specific constituency.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Pumwani",
      "county_id": 1,
      "constituency": "Kamukunji",
      "type": "ward"
    }
  ]
}
```

---

### Social Authentication Endpoints

#### GET /auth/{provider}/redirect

Redirect to OAuth provider for authentication.

**Providers:** `google`, `github`, `twitter`

**Query Parameters:**
- `role`: User role for registration (optional)

**Response (302):** Redirect to provider

#### GET /auth/{provider}/callback

Handle OAuth provider callback.

**Response (302):** Redirect to application with token or error

#### GET /auth/social/accounts

Get user's connected social accounts.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "provider": "google",
      "nickname": "johndoe",
      "name": "John Doe",
      "email": "user@example.com",
      "avatar": "https://lh3.googleusercontent.com/...",
      "connected_at": "2026-03-25T19:19:00.000000Z",
      "has_valid_token": true
    }
  ]
}
```

#### DELETE /auth/social/accounts/{provider}

Disconnect social account.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Social account disconnected successfully"
}
```

---

## Error Responses

### Standard Error Format

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Error message for field"]
  }
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

### Common Error Messages

```json
{
  "success": false,
  "message": "Authentication required",
  "errors": {
    "token": ["Invalid or expired token"]
  }
}
```

```json
{
  "success": false,
  "message": "Rate limit exceeded",
  "errors": {
    "rate_limit": ["Too many requests. Try again later."]
  }
}
```

---

## Rate Limiting

### Rate Limit Rules

- **Authentication endpoints**: 5 requests per minute
- **Profile endpoints**: 60 requests per minute
- **Geographical data**: 100 requests per minute
- **Public endpoints**: 30 requests per minute

### Rate Limit Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1648236400
```

---

## Data Validation

### Common Validation Rules

- **Email**: Must be valid email format
- **Password**: Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number
- **Name**: Required, max 255 characters
- **Phone**: Optional, valid phone number format
- **Website**: Optional, valid URL format
- **Avatar**: Image file, max 5MB, supported formats: JPG, PNG, GIF, WebP

### Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."],
    "avatar": ["The avatar must be an image file."]
  }
}
```

---

## Pagination

### Paginated Response Format

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 250,
    "last_page": 5,
    "from": 1,
    "to": 50
  },
  "links": {
    "first": "https://api.example.com/countries?page=1",
    "last": "https://api.example.com/countries?page=5",
    "prev": null,
    "next": "https://api.example.com/countries?page=2"
  }
}
```

---

## SDK Examples

### JavaScript/Fetch

```javascript
// Login
const response = await fetch('/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});

const data = await response.json();

if (data.success) {
  localStorage.setItem('token', data.token);
}
```

### PHP/Guzzle

```php
$client = new GuzzleHttp\Client();

$response = $client->post('https://api.example.com/api/auth/login', [
  'json' => [
    'email' => 'user@example.com',
    'password' => 'password123'
  ],
  'headers' => [
    'Accept' => 'application/json'
  ]
]);

$data = json_decode($response->getBody(), true);

if ($data['success']) {
    $token = $data['token'];
}
```

### Python/Requests

```python
import requests

response = requests.post('https://api.example.com/api/auth/login', json={
    'email': 'user@example.com',
    'password': 'password123'
})

data = response.json()

if data['success']:
    token = data['token']
```

---

## Webhooks

### Webhook Events

The application can send webhook notifications for various events:

- `user.created` - New user registration
- `user.verified` - Email verification completed
- `profile.updated` - User profile updated
- `social.connected` - Social account connected
- `social.disconnected` - Social account disconnected

### Webhook Configuration

Webhooks can be configured in the application settings or environment variables:

```env
WEBHOOK_URL=https://your-webhook-endpoint.com/receive
WEBHOOK_SECRET=your_webhook_secret_key
```

### Webhook Payload Format

```json
{
  "event": "user.created",
  "timestamp": "2026-03-25T19:19:00.000000Z",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  },
  "signature": "sha256=..."
}
```

---

## API Changelog

### v1.0.3 (Current)
- Added comprehensive API endpoints
- Implemented rate limiting
- Added webhook support
- Enhanced error responses

### v1.0.2
- Added geographical data endpoints
- Improved authentication flow

### v1.0.1
- Initial API release
- Basic authentication endpoints
- User profile management

---

## Support

For API support and questions:

- **Documentation**: https://docs.your-domain.com
- **Email**: api-support@your-domain.com
- **GitHub Issues**: https://github.com/MCBANKSKE/MCBANKSLARAVEL/issues

---

## License

This API is provided under the MIT License. See LICENSE file for details.
