# URL Shortener API

A robust and scalable URL shortening service built with Laravel. This project is designed for high performance, featuring a dual-driver authentication system, optimized caching, and detailed redirection analytics.

## Key Features

-   **Flexible Authentication**: Support for both **Laravel Sanctum** and **JWT (JSON Web Tokens)**, allowing for seamless integration with different frontend architectures.
-   **Advanced Caching**: Deeply integrated with the Laravel Cache system, with native support for **Redis** to ensure sub-millisecond link resolution.
-   **User Management**:
    -   **Admin Users**: Full control over users and system-wide links.
    -   **Regular Users**: Personal dashboard to manage and track individual short links.
-   **Analytics & Redirection**: Automatic counting of redirects with buffered persistence to minimize database load.
-   **Automated Emails**: Integrated system for welcome emails, link verifications, and password recovery.
-   **Interactive Documentation**: Fully documented via **Swagger UI** for an interactive API exploration experience.
-   **Tested & Reliable**: Comprehensive test suite developed using **PHPUnit**, covering core logic and edge cases.

## API Endpoints

### Public
-   `GET /{code}` - Redirect to the original URL.
-   `GET /health` - Check system status.

### Authentication
-   `POST /api/auth/register` - Register a new user.
-   `POST /api/auth/login` - Authenticate and receive a token.
-   `POST /api/auth/logout` - Revoke current session.
-   `GET /api/auth/user` - Get authenticated user details.
-   `POST /api/auth/validate-token` - Check token validity.
-   `POST /api/forgot-password` - Request reset link.
-   `POST /api/reset-password` - Execute password reset.

### User Profile
-   `GET /api/profile` - View your profile.
-   `PUT /api/profile` - Update profile data.
-   `DELETE /api/profile` - Delete your account.

### Short Links (Authenticated)
-   `GET /api/short-links` - List your links.
-   `POST /api/short-links` - Create a new short link.
-   `GET /api/short-links/{id}` - View link details.
-   `PUT /api/short-links/{id}` - Update a link.
-   `DELETE /api/short-links/{id}` - Delete a link.
-   `POST /api/short-links/bulk-delete` - Delete multiple links.

### Admin Tools
-   `GET /api/users` - List all users.
-   `POST /api/users` - Create a new user.
-   `GET /api/users/{id}` - View user details.
-   `PUT /api/users/{id}` - Modify user data.
-   `DELETE /api/users/{id}` - Remove a user.

---

## License

The project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
