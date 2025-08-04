<?php

namespace App\OpenApi;

// Import specific classes from OpenApi\Attributes
use OpenApi\Attributes\Info;
use OpenApi\Attributes\Server;
use OpenApi\Attributes\SecurityRequirement;
use OpenApi\Attributes\SecurityScheme;
use OpenApi\Attributes\Components;
use OpenApi\Attributes as OA; // Keep this alias for other attributes like @OA\Get, @OA\Post etc.


#[OA\OpenApi(
    openapi: "3.0.0",
    info: new Info( // Use the imported Info class
        version: "1.0.0",
        title: "Taxi Booking System API",
        description: "API documentation for the Taxi Booking System backend.",
        contact: new OA\Contact(name: "Mizanur Islam Laskar", email: "cicakemizan@gmail.com")
    ),
    servers: [
        new Server(url: "http://localhost:8000/api/v1", description: "Development Server") // Use the imported Server class
    ],
    security: [
        ["bearerAuth" => []] // Define security requirement as an array
    ],
    components: new Components( // Use the imported Components class
        securitySchemes: [
            new SecurityScheme( // Use the imported SecurityScheme class
                securityScheme: "bearerAuth",
                type: "http",
                scheme: "bearer",
                bearerFormat: "JWT"
            )
        ]
    )
)]
class OpenApiDefinition
{
    // This class still doesn't need any methods or properties.
    // Its sole purpose is to hold the global OpenAPI annotations.
}