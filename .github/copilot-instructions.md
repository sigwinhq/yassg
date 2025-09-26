# GitHub Copilot Instructions for YASSG

## Repository Overview

YASSG (Yet Another Static Site Generator) is a PHP-based static site generator that combines the power of Twig templating with Symfony Encore for asset management and uses YAML databases for content organization.

## Project Architecture

### Core Components

- **Static Site Generator**: Generates static HTML sites from Twig templates
- **YAML Database**: Uses YAML files for content and data organization
- **Asset Pipeline**: Symfony Encore for JavaScript/CSS bundling
- **CLI Interface**: Console commands for site generation and initialization
- **Multi-environment**: Supports different build environments (dev, prod)

### Key Directories

- `src/` - Core library code (PSR-4: `Sigwin\YASSG\`)
- `bin/yassg` - Main CLI entry point
- `config/` - Symfony configuration files
- `resources/init/` - Project initialization templates
  - `basic/` - Minimal project setup
  - `demo/` - Full-featured demo site
  - `gitlab/` - GitLab CI/Pages configuration
- `tests/` - Test suite including functional tests
- `web/` - Web assets and frontend code

## Development Environment

### Prerequisites

- PHP 8.3+ with ext-zlib
- Composer for dependency management
- Docker (for development tools)
- Make for build automation

### Setup

```bash
# Install dependencies
composer install

# Run tests
make test

# Run code analysis
make analyze

# Format code
make cs

# Prepare for distribution (runs all checks - local CI)
make dist
```

### Testing

The project uses PHPUnit with mutation testing via Infection:
- Unit tests in `tests/unit/`
- Functional tests in `tests/functional/`
- Integration tests validate full site generation

### Code Quality

- **PHP-CS-Fixer**: Code formatting (`.php-cs-fixer.dist.php`)
- **PHPStan**: Static analysis (`phpstan.neon.dist`)
- **Psalm**: Additional static analysis (`psalm.xml.dist`)
- **Rector**: Automated refactoring (`rector.php`)
- **Infection**: Mutation testing (`infection.json.dist`)

## Coding Standards

### PHP Code Style

- PSR-12 coding standard
- Strict types declaration required: `declare(strict_types=1);`
- MIT license header in all PHP files
- Prefer final classes and readonly properties where applicable
- Use type hints for all parameters and return types

### Naming Conventions

- Classes: PascalCase
- Methods/Properties: camelCase
- Constants: UPPER_SNAKE_CASE
- Namespaces follow PSR-4: `Sigwin\YASSG\`

## Key APIs and Components

### Core Classes

- `Generator`: Main site generation logic
- `Database`: Collection of content that can point to YAML files via Decoders
- `Route`: Represents site routes and pages
- `Storage`: File system abstraction
- `Bridge\Symfony\*`: Symfony integration layer

### CLI Commands

- `yassg:init`: Initialize new projects
- `yassg:generate`: Generate static sites
- `yassg:validate`: Validate YAML database

### Configuration

- `config/services.yaml`: Service container configuration
- `config/packages/`: Symfony bundle configurations
- `config/routes/yassg.yaml`: Internal routing

## Build System

### Makefile Targets

- `make test`: Run full test suite with mutation testing
- `make analyze`: Run static analysis (PHPStan, Psalm)
- `make dist`: Format code and prepare for distribution
- `make help`: Show available targets

### Docker Integration

Uses `jakzal/phpqa` for consistent development environment across different platforms.

## Site Generation Workflow

1. **Initialization**: `yassg:init` creates project structure
2. **Content**: YAML files define pages, routes, and data
3. **Templates**: Twig templates in `templates/` directory
4. **Assets**: Frontend assets processed by Encore
5. **Generation**: `yassg:generate` produces static HTML
6. **Output**: Generated site in `public/` directory

## Testing Strategy

### Functional Tests

- `tests/functional/init/`: Tests project initialization
- `tests/functional/site/`: Tests complete site generation
- Both include fixtures and expected output validation

### Running Specific Tests

```bash
# Run specific test suite
vendor/bin/phpunit tests/unit/
vendor/bin/phpunit tests/functional/

# Run with coverage
make test
```

## Common Patterns

### Database Operations

```php
$database = new MemoryDatabase();
$database->set('key', $value);
$result = $database->get('key');
```

### Route Definition

```php
$route = new Route('path', $metadata, $content);
```

### Storage Operations

```php
$storage = new Storage($filesystem);
$storage->write($path, $content);
```

## Troubleshooting

### Common Issues

1. **Composer install fails**: Ensure PHP 8.3+ and required extensions
2. **Docker permission errors**: Check user permissions for Docker
3. **Build failures**: Verify all dependencies are installed
4. **Test timeouts**: Increase timeout values in phpunit.xml.dist

### Debug Mode

Set environment variables for debugging:
- `APP_DEBUG=1`: Enable Symfony debug mode
- `YASSG_SKIP_BUNDLES`: Skip specific Symfony bundles

## Contributing Guidelines

1. Follow existing code style and conventions
2. Add tests for new functionality
3. Run `make dist` before committing
4. Ensure all CI checks pass
5. Update documentation for public API changes

## Dependencies

### Core Dependencies

- Symfony Framework Bundle (^6.4 || ^7.0)
- Twig (via symfony/twig-bundle)
- League CommonMark (Markdown processing)
- Embed/Embed (Media embedding)

### Development Dependencies

- PHPUnit (^11.4 || ^12.0)
- Sigwin Infra (build tools and configuration)

## License

MIT License - see LICENSE file for details.