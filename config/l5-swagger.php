<?php
return [
    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false), // Add this line
    'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
    'defaults' => [
        'routes' => [
            'docs' => 'api/documentation',
            'api' => 'api-docs.json',
        ],
        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => resource_path('views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', '/api'),
            'excludes' => [],
            'annotations' => base_path('app'), // Ensure this line exists
            'docs_json' => 'api-docs.json',
            'docs_yaml' => 'api-docs.yaml',
        ],
        'info' => [
            'title' => 'Job Board API',
            'description' => 'API documentation for the Job Board application.',
            'version' => env('L5_SWAGGER_API_VERSION', '1.0.0'),
        ],
        // ... other settings
    ],
    'security' => [
        'default' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
            'description' => 'Enter your Bearer token in the format **Bearer {token}**',
        ],
    ],
];
