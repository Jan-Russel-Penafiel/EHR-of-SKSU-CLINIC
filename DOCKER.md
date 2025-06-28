# Docker Setup for EHR System

This document provides instructions for setting up and running the EHR (Electronic Health Records) system using Docker.

## Prerequisites

- Docker installed on your system
- Docker Compose installed on your system

## Getting Started

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd ehr
   ```

2. Build and start the containers:
   ```bash
   docker-compose up -d
   ```

   This will:
   - Build the PHP/Apache web server
   - Start the MySQL database
   - Start the phpMyAdmin service
   - Initialize the database with the schema in ehrdb.sql

3. Access the application:
   - Web application: http://localhost:8080
   - phpMyAdmin: http://localhost:8081 (use username `root` and password ``)

## Container Information

- **Web Server**: PHP 8.2 with Apache
  - Port: 8080
  - Contains the application code

- **Database**: MySQL 8.0
  - Port: 3307
  - Database name: ehrdb
  - Username: root
  - Password: 
  - Root password: 

- **phpMyAdmin**:
  - Port: 8081
  - Use for database management

## Persisted Data

Database data is persisted using a named Docker volume (`mysql_data`).

## Environment Variables

The application uses the following environment variables:

- `DB_CONNECTION`: Database connection type (mysql)
- `DB_HOST`: Database hostname (lccalhost)
- `DB_PORT`: Database port (3306)
- `DB_DATABASE`: Database name (ehrdb) 
- `DB_USERNAME`: Database username (root)
- `DB_PASSWORD`: Database password ()

## Common Commands

- Start the containers:
  ```bash
  docker-compose up -d
  ```

- Stop the containers:
  ```bash
  docker-compose down
  ```

- View container logs:
  ```bash
  docker-compose logs
  ```

- Rebuild containers after changes:
  ```bash
  docker-compose up -d --build
  ```

- Access the web container shell:
  ```bash
  docker-compose exec web bash
  ```

- Access the database shell:
  ```bash
  docker-compose exec db mysql -u root -p ehrdb
  ```

## Troubleshooting

- **Database connection issues**: Make sure the database container is running and healthy
- **Permission issues**: The application files should be owned by www-data in the container
- **PHP errors**: Check the Apache logs using `docker-compose logs web` 