# Running the Laravel Project

## Prerequisites

Before running the Laravel project, ensure you have the following installed:

- PHP (version 7.3 or higher)
- Composer
- A web server (like Apache or Nginx)
- MySQL supported database

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ShashwatSheth27/eVitalRxTask.git
   cd eVitalRxTask
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Copy the environment file:**
   ```bash
   cp .env.example .env
   ```

4. **Update environment variables:**
   Open the `.env` file and update the following variables as needed:
   - `DB_CONNECTION`: Your database connection (e.g., `mysql`)
   - `DB_HOST`: Database host (usually `127.0.0.1`)
   - `DB_PORT`: Database port (usually `3306`)
   - `DB_DATABASE`: Your database name (e.g., `eVitalRx`)
   - `DB_USERNAME`: Your database username (e.g., `root`)
   - `DB_PASSWORD`: Your database password (usually ``)
   - `MAIL_MAILER`: `smtp`
   - `MAIL_HOST`: `smtp.gmail.com`
   - `MAIL_PORT`: `587`
   - `MAIL_USERNAME`: Your gmail id (e.g., `shethshashwat26@gmail.com`)
   - `MAIL_PASSWORD`: Your gmail app password (e.g., `ydge vnnb ctse rtfd`)
   - `MAIL_ENCRYPTION`: `null`
   - `MAIL_FROM_ADDRESS`: email from address (e.g., `shethshashwat26@gmail.com`)
   - `MAIL_FROM_NAME`: email from name (e.g., `eVitalRx`)
   - Update any other relevant API endpoints or keys.

5. **API Endpoints:**
   
    Here are the available API endpoints:
   - **POST /api/signup**: Register a new user.
   - **POST /api/verify-email**: Verify the email address using an OTP.
   - **POST /api/login**: Authenticate a user and log them in.
   - **POST /api/forgot-password**: Initiate the password reset process.
   - **POST /api/reset-password**: Reset the user's password.
   - **POST /api/profile/update**: Update the authenticated user's profile (requires authentication).

6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

7. **Serve the application:**
   You can use the built-in PHP server for development:
   ```bash
   php artisan serve
   ```
   Access the application at `http://localhost:8000`.

## Additional Configuration

- For any additional configurations, refer to the [Laravel documentation](https://laravel.com/docs).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).