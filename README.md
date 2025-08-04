# TAXI Booking API

This project provides a robust and scalable API backend for a taxi booking system. It's built with a focus on clean architecture, maintainability, and clear separation of concerns, utilizing modern PHP practices and a custom MVC-like structure.

## Business Insights

- `Centralized Configuration:` The Common Settings API allows administrators to easily manage global settings such as company details, contact information, holiday surcharges, night charges, and various payment processing fees. This ensures consistency across the system and quick adjustments to business rules.

- `Dynamic Pricing & Vehicle Management:`

  - The Cars API enables the management of different vehicles, their features, base fares, and minimum fares.
  - The Slabs API defines distance or hourly segments, allowing for flexible pricing models.
  - The Car Slab Fares API links specific cars to these slabs with corresponding fare amounts, enabling granular control over pricing based on vehicle type and distance/duration.

- `Location-Based Charges:`
  - Airports API helps manage airport-specific tax/toll charges, crucial for accurate fare calculation for airport transfers.
  - Tunnel Charges API allows for setting specific charges for tunnels based on date ranges, accommodating variable pricing for infrastructure usage.
  - Extra Charges / Toll API provides flexibility to define additional charges for specific geographical areas (identified by zip codes), handling unique local tolls or surcharges.

By providing these structured APIs, the system facilitates accurate fare calculation, streamlined operations, and better customer service, all managed through a well-defined and accessible interface.

## Technical Overview

This project adheres to a clean architecture, separating different layers of the application to ensure maintainability, testability, and scalability.

Core Design Patterns & Components:

1. `Model:` Represents the data structure and business entities (e.g., Car, Airport, TunnelCharge). Models are responsible for defining data attributes and methods for converting data to/from arrays (for API responses and database operations).

2. `Repository Pattern:`

   - Contracts (Interfaces): Define the contract for data persistence operations (e.g., AirportInterface, CarInterface). This abstracts the data storage mechanism.

   - MySQL (Implementations): Provide the concrete implementation of these interfaces using PDO for MySQL database interactions (e.g., AirportDatabase, CarDatabase).

   - Benefits:

     - Decoupling: Business logic (in Services) is decoupled from data access logic. You can swap out the database (e.g., from MySQL to PostgreSQL) without altering the Service layer.

     - Testability: Repositories can be easily mocked for unit testing the Service layer.

     - Centralized Data Access: All database operations for a specific entity are in one place.

   - Service Layer:

     - Contains the core business logic of the application (e.g., AirportService, CarService).

     - Services orchestrate operations, interact with one or more repositories, and apply business rules and validations.

     - Benefits:

       - Business Logic Encapsulation: All business rules are centralized, preventing "fat controllers" or scattered logic.

       - Transaction Management: Can manage complex transactions involving multiple repository operations.

       - Reusability: Business logic can be reused across different controllers or even other interfaces (e.g., a CLI tool).

   - Controller Layer:

     - Handles incoming HTTP requests, delegates tasks to the Service layer, and prepares HTTP responses.

     - Controllers are kept "thin" – they primarily deal with request parsing, input validation (via Form Requests), and response formatting.

     - Benefits:

       - Single Responsibility: Focuses solely on handling HTTP requests and responses.

       - Readability: Easy to understand the flow of an API endpoint.

   - Form Request Objects (src/Http/Requests):

     - Dedicated classes for handling input validation for specific API endpoints.

     - They define validation rules and custom error messages.

     - Benefits:

       - Centralized Validation: Keeps validation logic out of controllers, making them cleaner.

       - Reusability: If the same validation is needed for multiple endpoints, the Form Request can be reused.

       - Self-Documenting: The rules clearly define expected input.

   - Dependency Injection:

     - Services and Repositories are "injected" into their consumers (e.g., a Service is injected into a Controller, a Repository into a Service) rather than being instantiated directly within them.

     - Benefits:

       - Loose Coupling: Components are less dependent on concrete implementations, making the system more flexible.

       - Testability: Allows easy swapping of dependencies with mock objects during testing.

   - Swagger/OpenAPI Documentation:

     - API endpoints are automatically documented using PHP attributes (#[OA\] annotations).

     - A swagger.php script generates a swagger.json file, which can be served by Swagger UI for interactive API exploration.

     - Benefits:

       - Automated Documentation: Reduces manual effort and keeps documentation in sync with code.

       - Interactive API Explorer: Provides a user-friendly interface for testing and understanding API endpoints.

       - Consistency: Ensures a standardized approach to API documentation.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

1. PHP: Version 8.2 or higher.
2. Composer: PHP dependency manager.
3. MySQL or MariaDB: Database server.
4. Web Server: Apacheor use PHP's built-in web server for development. XAMPP/WAMP are good all-in-one solutions.

## Installation Guide

1. Unzip the attached zip file and open the folder in your terminal.

2. Provide read & write accesses to the 'public' and 'log' folders (along their contents within).

3. Install dependencies
   ```sh
   composer install
   ```
4. `Create Database:` Log in to your MySQL/MariaDB server (e.g., via phpMyAdmin or MySQL client) and create a new database. For example, "taxi_booking_db".

5. `Adjust Environment:` Open the ".env" file with your editor and adjust your database information from line number 5 to 8 (DB_HOST, DB_NAME, DB_USER, DB_PASS).

6. Creaate the tables in the database:

   ```sh
   php cli.php migrate
   ```

7. Insert sample data:

   ```sh
    php cli.php seed SlabSeeder
    php cli.php seed
   ```

   These will create sample data in some tables like 'airports', 'cars', 'car_slab_fares', 'google_api_keys', 'sms_services', and 'slabs'.

8. Generate OpenAPI (Swagger) Documentation:

   ```sh
   php swagger.php
   ```

9. Run the application:

   ```sh
   php -S localhost:8000 -t public/
   ```

   This will start the server on "http://localhost:8000".

10. Open a browser and open http://localhost:8000/swagger-ui. You'll see all the APIs, which I've demonstrated in the videos. You can update the header information by editing the 'src\OpenApi\OpenApiDefinition.php' file (title, description, contact).

## Understanding the Database (IMPORTANT)

Before playing with the APIs, it is very essential to check the database. The best route for this to check the migration files under 'src\Database\Migrations' and seeders within 'src\Database\Seeders' directories.

1. `Important Migrations to Check:`

- 2025_07_21_160254_createslabstable.php
- 2025_07_21_191301_createcarstable.php
- 2025_07_22_070521_createcarslabfarestable.php
- 2025_07_21_191150_createcommonsettingstable.php

2. `Important Seeders to Check (seeders are used to unsert sample data):`

- CarSeeder.php
- SlabSeeder
- CarSlabFareSeeder.php
