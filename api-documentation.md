# Job Board System API Documentation

## Base URL
- `http://localhost:8000/api`

## 1. Register a New User
- **Endpoint**: `POST /auth/register`
- **Description**: Register a new user as either an employer or job_seeker.
- **Request Body** (JSON):
  ```json
  {
      "name": "string",
      "email": "string|email|unique",
      "password": "string|min:8",
      "password_confirmation": "string|same:password",
      "type": "string|enum:employer,job_seeker"
  }

- **Success Response** (201):
  ```json
  {
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "Test Employer",
        "email": "testemployer@jobboard.com",
        "type": "employer",
        "created_at": "2023-10-01T12:00:00.000000Z",
        "updated_at": "2023-10-01T12:00:00.000000Z"
    },
    "access_token": "1|your_token_here"
  }

- **Error Response** (422):
  ```json
  {
    "error": {
        "email": ["The email field is required."],
        "password": ["The password field must be at least 8 characters."]
    }
  }

## 2. Login
- **Endpoint**: `POST /auth/login`
- **Description**: Authenticate a user and return an access token.
- **Request Body** (JSON):
  ```json
    {
        "email": "string|email",
        "password": "string"
    }


- **Success Response**(200):
  ```json
  {
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Test Employer",
        "email": "testemployer@jobboard.com",
        "type": "employer",
        "created_at": "2023-10-01T12:00:00.000000Z",
        "updated_at": "2023-10-01T12:00:00.000000Z"
    },
    "access_token": "1|your_token_here"
  }
- **Error Response**(401):
  ```json
  {
    "error": "Invalid credentials"
  }


## 3. List All Jobs
- **Endpoint**: `GET /jobs`
- **Description**: Retrieve a list of all job postings (publicly accessible).
- **Request Body**: None
- **Success Response** (200):
  ```json
  {
      "message": "Jobs retrieved successfully",
      "data": [
          {
              "id": 1,
              "user_id": 2,
              "title": "Software Engineer",
              "description": "Develop and maintain web applications.",
              "location": "Remote",
              "salary": 60000,
              "type": "full_time",
              "application_deadline": "2025-12-31",
              "created_at": "2025-03-15T10:00:00.000000Z",
              "updated_at": "2025-03-15T10:00:00.000000Z"
          }
      ]
  }
