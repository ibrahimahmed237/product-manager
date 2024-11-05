# product-manager
project/
├── .env.example
├── .env
├── .gitignore
├── composer.json
├── composer.lock
├── phpunit.xml
├── phpcs.xml
├── phpstan.neon
├── README.md
├── public/
│   └── index.php
├── src/
│   ├── Config/
│   │   ├── Database.php
│   │   └── Environment.php
│   ├── Core/
│   │   ├── Application.php
│   │   ├── Router.php
│   ├── Controllers/
│   │   └── ProductController.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── DVD.php
│   │   ├── Book.php
│   │   └── Furniture.php
|   ├── Repositories/
│   │   ├── Interfaces/
│   │   │   ├── ProductRepositoryInterface.php
│   │   ├── ProductRepository.php
│   ├── Services/
│   │   ├── ProductService.php
│   │   └── ValidationService.php
│   ├── Exceptions/
│   │   ├── Handler.php
│   │   └── ValidationException.php
│   └── Utils/
│       └── Logger.php
├── tests/
│   ├── Unit/
│   │   └── Models/
│   └── Integration/
│       └── Controllers/
├── logs/
│   └── .gitkeep
├── storage/
│   └── .gitkeep
└── vendor/
|── frontend/