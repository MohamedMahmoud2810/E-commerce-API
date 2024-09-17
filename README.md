Laravel - E-commerce API
Overview
This project is a Laravel-based comprehensive API for an e-commerce platform designed to be consumed by a Flutter application. The API supports core e-commerce functionality such as user management, product and order management, payment integration, reviews, real-time notifications, and more. It demonstrates best practices in API design, security, performance optimization, and testing.


Features
1. User Management
User registration, login, password reset, and email verification.
JWT-based authentication.
Role-based access control using Spatie Laravel-Permission (Admin, Vendor, Customer).
2. Product Management
CRUD operations for products.
Product categories and tags.
Vendors can manage their own products.
Product image upload and management.
3. Order Management
Customers can create, view, and cancel orders.
Vendors can manage and update their order statuses.
Order tracking system.
4. Payment Integration
Stripe integration for payments.
Pusher integration for payment notifications and status updates.
5. Review and Rating System
Customers can review and rate products.
Spam detection and moderation of reviews.
6. Search and Filtering
Full-text search functionality for products.
Filter by category, price range, rating, and more.
Performance optimization with indexing and caching.
7. Real-time Notifications
Implemented using Laravel Echo and Pusher.
Real-time notifications for order status updates, new products, promotions, and more.
8. API Documentation
API documentation generated using Swagger.
Comprehensive and easy-to-understand documentation.
9. Testing
Unit and integration tests with 90%+ test coverage.
Reports and logs of test results.
10. Performance Optimization
Caching strategies using Redis or Memcached for better performance.
Optimized database queries and indexing for faster responses.
11. Security
Rate limiting to prevent abuse.
Protection against common vulnerabilities like SQL Injection, XSS, CSRF.
Secure handling of sensitive data with encryption and hashing.

Installation and Setup
Prerequisites
PHP 8.x
Composer
Docker (for containerized development)
Redis (optional, for caching)
MySQL/PostgreSQL Database
Pusher account (for real-time notifications)
Stripe account (for payment gateway)
Node.js & npm (for Laravel Echo and frontend build)
Step-by-Step Installation
Clone the repository:


git clone https://github.com/yourusername/laravel-recruitment-assignment.git
cd laravel-recruitment-assignment


Install dependencies:
composer install
Environment setup: Copy the .env.example to .env and update the configuration.


cp .env.example .env
php artisan key:generate
Set up database:

Configure the database settings in the .env file.
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
Run migrations and seeders:

php artisan migrate --seed
Install JWT:

php artisan jwt:secret
Run the application:

php artisan serve
