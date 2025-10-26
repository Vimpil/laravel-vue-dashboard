# Betting Mini-Service

This is a mini-service for betting, built with Laravel for the backend and Vue.js for the frontend.

## Project Description

The goal of this project is to implement a simple betting service with a focus on security and reliability.

### Features

#### Backend (Laravel)
-   **Authentication**: User authentication using Laravel Sanctum.
-   **API Endpoints**:
    -   `POST /api/bets`: Create a new bet.
    -   `GET /api/bets`: Get the current user's bets.
    -   `GET /api/events`: Get a list of available events.
-   **Transactions**: When a bet is placed, the user's balance is updated, and a transaction is recorded.
-   **Double-Spend Protection**: Prevents users from spending more than their available balance, even with concurrent requests.
-   **Idempotency**: Requests with the same `Idempotency-Key` will not be processed more than once.
-   **Rate Limiting**: Limits users to 10 requests per minute for betting endpoints.
-   **Request Signing**: Verifies the `X-Signature` (HMAC) header to protect against tampering.
-   **Fraud Detection**: Logs suspicious activities to a `fraud_logs` table.

#### Frontend (Vue.js)
-   A simple form to create a bet, allowing users to select an event, outcome, and amount.
-   Client-side validation to ensure the bet amount is valid.
-   Error handling for issues like insufficient funds or rate limiting.

## Getting Started

### Prerequisites

-   Docker
-   Docker Compose

### Installation and Running the Application

1.  Clone the repository:
    ```bash
    git clone https://github.com/your-username/betting-mini-service.git
    cd betting-mini-service
    ```

2.  Create a `.env` file in the root directory of the project and add the following lines:
    ```
    DOCKER_BUILDKIT=1
    COMPOSE_DOCKER_CLI_BUILD=1
    ```

3.  Build and run the application using Docker Compose:
    ```bash
    docker-compose up --build
    ```

4.  After the application is running, execute the database migrations and seeders to populate the database with initial data:
    ```bash
    docker-compose exec app php artisan migrate
    docker-compose exec app php artisan db:seed
    ```

The application will be available at the following URLs:
- **Backend API**: `http://localhost:8080`
- **Frontend (Vue.js)**: `http://localhost:8081`

## Architectural Decisions

-   **Backend**: Laravel was chosen for its robust features, including built-in support for APIs, authentication, and database management.
-   **Frontend**: Vue.js was selected for its simplicity and ease of use in creating interactive user interfaces.
-   **Database**: MySQL is used as the database.
-   **Containerization**: The entire application is containerized using Docker, ensuring a consistent environment for development and deployment.

## Security Measures

-   **Authentication**: Laravel Sanctum is used for API token-based authentication, ensuring that only authenticated users can access protected endpoints.
-   **Double-Spend Protection**: The application uses database transactions to ensure that a user's balance is updated atomically, preventing race conditions that could lead to double-spending.
-   **Idempotency**: The `Idempotency-Key` header is used to prevent duplicate transactions. Each key is stored, and subsequent requests with the same key are rejected.
-   **Rate Limiting**: Laravel's built-in rate limiter is used to restrict the number of requests a user can make to the betting endpoints, mitigating brute-force attacks.
-   **Request Signing**: The `X-Signature` header, an HMAC hash of the request payload, is used to verify the integrity and authenticity of incoming requests.
-   **Fraud Logging**: Any suspicious activities, such as multiple failed validation attempts or unusual betting patterns, are logged for review.

## Testing

The business logic is covered by unit and feature tests to ensure correctness. Key areas tested include:
-   **API Route Availability**: Verifies that all API endpoints (`/api/login`, `/api/logout`, `/api/user`, `/api/events`, `/api/bets`) are responsive.
-   **Successful Bet Creation**: Ensures a bet is created and the user's balance is correctly debited.
-   **Insufficient Funds**: Confirms that a user cannot place a bet if their balance is too low.
-   **Idempotency**: Guarantees that identical requests sent with the same `Idempotency-Key` do not result in duplicate bets.
-   **Data Retrieval**: Checks that a user can fetch their list of bets and the list of available events.

To run the tests, execute the following command:
```bash
docker-compose exec app php artisan test
```

## Database Migrations and Seeding

The project includes database migrations to create the necessary tables and seeders to populate the database with initial data (users and events).

To run the migrations and seed the database, use the following commands after starting the application with `docker-compose up --build`:

```bash
# Run database migrations
docker-compose exec app php artisan migrate

# Run database seeders
docker-compose exec app php artisan db:seed
```
