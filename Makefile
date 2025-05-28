# Makefile for WordPress + MySQL + phpMyAdmin project

# Define variables
TIMESTAMP=$(shell date +%F_%H-%M-%S)
BACKUP_DIR=./mysql-backups/$(TIMESTAMP)
BACKUP_FILE=$(BACKUP_DIR)/dockerwp_db_backup.sql
CONTAINER_NAME=dockerwp-mysql
DB_USER=dockerwp
DB_PASSWORD=dockerwp_password
DB_NAME=dockerwp_db

# Backup the MySQL database from Docker container
dbbackup:
	@echo "Creating backup directory: $(BACKUP_DIR)"
	@mkdir -p $(BACKUP_DIR)
	@echo "Running mysqldump from container: $(CONTAINER_NAME)"
	@docker exec $(CONTAINER_NAME) /usr/bin/mysqldump -u $(DB_USER) -p$(DB_PASSWORD) $(DB_NAME) > $(BACKUP_FILE)
	@echo "Backup saved to $(BACKUP_FILE)"

# Restore the MySQL database inside the container
# Usage: make dbrestore FILE=./mysql-backups/2025-05-20_10-00-00/dockerwp_db_backup.sql
dbrestore:
ifndef FILE
	$(error Please provide a backup file to restore using: make dbrestore FILE=path/to/file.sql)
endif
	@echo "Restoring database '$(DB_NAME)' from host file $(FILE)"
	@cat $(FILE) | docker exec -i $(CONTAINER_NAME) /usr/bin/mysql -u $(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)
	@echo "Database restored from $(FILE)"

# Example Restore command: make dbrestore FILE=./mysql-backups/2025-05-20_12-38-18/dockerwp_db_backup.sql


# Clean all but the latest 3 backups
clean:
	@echo "Cleaning all but the latest 3 backups"
	@ls -dt ./mysql-backups/* | tail -n +2 | xargs rm -rf || true
	@echo "Old backups cleaned, keeping the latest 3"

# Start the Docker containers
up:
	@echo "Starting Docker containers"
	@docker-compose up -d

# Stop the Docker containers
down:
	@echo "Stopping Docker containers"
	@docker-compose down

# List running Docker containers
ps:
	@echo "Listing running Docker containers"
	@docker ps

# Build Docker containers
build:
	@echo "Building Docker containers"
	@docker-compose build

# Setup environment (build + up)
setup:
	@echo "Setting up the environment"
	@make build
	@make up