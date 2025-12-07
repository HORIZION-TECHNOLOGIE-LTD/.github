# ChiBank/QRPay Makefile
# Convenient commands for development and deployment

.PHONY: help install build dev deploy docker-build docker-up docker-down clean release

# Default target - show help
help:
	@echo "ChiBank/QRPay - Available Commands"
	@echo "=================================="
	@echo ""
	@echo "Development:"
	@echo "  make install       - Install all dependencies"
	@echo "  make build         - Build frontend assets"
	@echo "  make dev           - Start development server"
	@echo "  make clean         - Clean build artifacts and caches"
	@echo ""
	@echo "Production:"
	@echo "  make build-prod    - Build for production"
	@echo "  make deploy        - Deploy to production"
	@echo "  make release       - Create release package (软包)"
	@echo ""
	@echo "Docker:"
	@echo "  make docker-build  - Build Docker image"
	@echo "  make docker-up     - Start Docker containers"
	@echo "  make docker-down   - Stop Docker containers"
	@echo "  make docker-logs   - View Docker logs"
	@echo ""
	@echo "Testing:"
	@echo "  make test          - Run tests"
	@echo "  make lint          - Run code linting"
	@echo ""

# Install dependencies
install:
	@echo "Installing Composer dependencies..."
	composer install
	@echo "Installing NPM dependencies..."
	npm install
	@echo "Setting up environment..."
	@if [ ! -f .env ]; then cp .env.example .env && php artisan key:generate; fi
	@echo "Installation complete!"

# Build frontend assets
build:
	@echo "Building frontend assets..."
	npm run build
	@echo "Build complete!"

# Build for production
build-prod:
	@echo "Building for production..."
	composer install --no-dev --optimize-autoloader --no-interaction
	npm ci
	npm run build:prod
	@echo "Production build complete!"

# Start development server
dev:
	@echo "Starting development server..."
	php artisan serve

# Deploy to production
deploy:
	@echo "Deploying to production..."
	./scripts/deploy.sh

# Build Docker image
docker-build:
	@echo "Building Docker image..."
	./scripts/docker-build.sh

# Start Docker containers
docker-up:
	@echo "Starting Docker containers..."
	docker-compose up -d
	@echo "Containers started!"
	@echo "Run 'make docker-logs' to view logs"

# Stop Docker containers
docker-down:
	@echo "Stopping Docker containers..."
	docker-compose down
	@echo "Containers stopped!"

# View Docker logs
docker-logs:
	docker-compose logs -f app

# Enter Docker container
docker-shell:
	docker-compose exec app sh

# Run tests
test:
	@echo "Running tests..."
	php artisan test

# Run code linting
lint:
	@echo "Running PHP linter..."
	composer exec --no-interaction -- vendor/bin/pint || echo "Pint not available"

# Clean build artifacts and caches
clean:
	@echo "Cleaning build artifacts..."
	rm -rf public/build/
	rm -rf node_modules/
	rm -rf vendor/
	@echo "Cleaning Laravel caches..."
	php artisan cache:clear || true
	php artisan config:clear || true
	php artisan route:clear || true
	php artisan view:clear || true
	@echo "Clean complete!"

# Fresh install (clean + install)
fresh: clean install
	@echo "Fresh installation complete!"

# Database operations
db-migrate:
	@echo "Running database migrations..."
	php artisan migrate

db-seed:
	@echo "Seeding database..."
	php artisan db:seed

db-fresh:
	@echo "Fresh database setup..."
	php artisan migrate:fresh --seed

# Optimize for production
optimize:
	@echo "Optimizing for production..."
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	composer dump-autoload --optimize --classmap-authoritative
	@echo "Optimization complete!"

# Create release package (软包)
release:
	@echo "Creating release package..."
	@if [ -z "$(VERSION)" ]; then \
		echo "Usage: make release VERSION=5.0.0"; \
		exit 1; \
	fi
	chmod +x scripts/create-release.sh
	./scripts/create-release.sh $(VERSION)
	@echo "Release package created successfully!"
