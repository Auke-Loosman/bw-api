# BakerWare Challenge -- API

## Overview

This project is a Symfony-based REST API built as part of a technical
assessment.\
It provides full CRUD functionality for managing messages and implements
clean architecture principles, test-driven development, and a small
business logic layer using the Strategy pattern.

------------------------------------------------------------------------

## Tech Stack

-   PHP 8.2+
-   Symfony 7
-   Doctrine ORM
-   SQLite (development and test)
-   PHPUnit 11
-   NelmioCorsBundle

------------------------------------------------------------------------

## Architecture Overview

The project follows a layered structure:

-   Controller layer -- HTTP handling and orchestration
-   Service layer -- Business logic and entity manipulation
-   Registry + Handlers -- Strategy pattern for message processing
-   DTO layer -- Input validation and separation from entities
-   Doctrine ORM -- Persistence layer

### Key Architectural Decisions

-   Thin controllers
-   Business logic inside services
-   DTO-based validation using Symfony Validator
-   Strategy pattern for message type handling
-   Console command for message processing
-   Full test isolation using SQLite test database
-   Backwards-compatible API improvements (optional filtering)

------------------------------------------------------------------------

## Available Endpoints

### Create Message

POST `/api/messages`

### Get All Messages

GET `/api/messages`

### Get Messages Filtered by Type

GET `/api/messages?type=incoming`

### Get Single Message

GET `/api/messages/{id}`

### Update Message

PUT `/api/messages/{id}`

### Delete Message

DELETE `/api/messages/{id}`

------------------------------------------------------------------------

## Message Processing

Messages are processed via a console command:

php bin/console app:process-messages

Processing behavior is delegated to specific handlers:

-   IncomingHandler
-   OutgoingHandler
-   TaskHandler

Handler selection is performed through a MessageHandlerRegistry.

------------------------------------------------------------------------

## Installation

composer install

------------------------------------------------------------------------

## Database Setup

php bin/console doctrine:database:create php bin/console
doctrine:migrations:migrate

------------------------------------------------------------------------

## Running the Application

symfony server:start --no-tls

API available at: http://localhost:8000

------------------------------------------------------------------------

## Running Tests

php bin/phpunit

Tests use an isolated SQLite test database and recreate schema before
each test.

------------------------------------------------------------------------

## Testing Strategy

The project was developed using Test-Driven Development:

1.  Write failing test
2.  Implement minimal solution
3.  Expand assertions
4.  Refactor

Coverage includes:

-   Full CRUD lifecycle
-   Database persistence
-   Processing logic
-   Console command behavior
-   Test database isolation

------------------------------------------------------------------------

## Design Considerations

-   Clear separation of concerns
-   No business logic inside controllers
-   DTO validation to decouple API from entities
-   Strategy pattern instead of conditionals
-   Backwards-compatible API enhancements
-   Clean integration with frontend SPA
