# Senior PHP Backend Engineer Home Assignment

## Task

Build a Multi-Step User Onboarding Form for a Subscription Service

## Context

You are tasked with building part of an application for a company called *StreamPlus*,
a subscription-based streaming service that offers both free and premium memberships.
As part of their onboarding process, users need to fill out a multi-step  registration form that collects
personal details, address information, and payment details (if the user selects a premium subscription).
Your job is to implement this multi-step form using either Symfony or Laravel.

## Description

You will create a multi-step onboarding form that walks users through the following steps

### Step 1: User Information

* Collect basic user details such as:
  * Name
  * Email
  * Phone Number
  * Subscription type: Free or Premium
* If the user selects Premium, the next step will ask for payment information. If the
  user selects Free, skip to the confirmation step.
* Validation
  * Email must be valid and unique.
  * Phone number must be valid (at least 10 digits)

### Step 2: Address Information

* Fields to collect
  * Address Line 1
  * Address Line 2 (optional)
  * City
  * Postal Code
  * State/Province
  * Country (dropdown list)

### Step 3: Payment Information

* This step should only appear if the user has selected the *Premium* subscription in Step 1.
* Fields to collect
  * Credit Card Number
  * Expiration Date (MM/YY)
  * CVV
* Validation
  * Credit card number must be valid.
  * Expiration date must be in the future.
  * CVV should be exactly 3 digits.
  * You just need to collect and store this information, no payment integration is required.

### Step 4: Confirmation Page

* Display a summary of the user's input from the previous steps, including personal
  details, address, and (if applicable) payment information (obfuscate the CC number if
  applicable)
* Provide a final "Submit" button to save the data.

## Requirements

* Dynamic Steps
  * The form should dynamically adjust based on user input
    * If the user selects Free subscription, skip the payment step.
    * The address fields in Step 2 should change based on the country.
* Validation
  * Apply different validation rules on each step, ensuring all required fields are
    properly validated before advancing to the next step.
  * Implement custom validation based on the user's input (e.g., different address
    requirements for different countries).
* Form Navigation
  * Allow users to go back to previous steps to update information without losing
    data from the other steps.
* Data Persistence
  * Upon successful completion, store the collected data in a relational database
    (e.g., MySQL) with a proper structure to handle each step's data.
* Framework
  * You are free to use Symfony or Laravel, but please ensure to follow best
    practices and demonstrate advanced knowledge of the framework you
    choose (routing, services, validation, etc.).
* UI
  * Use Bootstrap 5 for the front-end elements

## Bonus

* Implement JavaScript to enhance the user experience by dynamically updating the
  form fields without reloading the page (using Symfony Stimulus or Laravel
  Livewire/Alpine.js).
* Show validation errors dynamically as the user fills out the form.
* Handle strings properly to be ready for localization
x
## Evaluation Criteria

* *Code Structure & Readability*: How well-organised and readable your code is,
  following best practices and the framework's conventions.
* *Advanced Framework Usage*: Your ability to leverage advanced features of
  Symfony or Laravel (e.g., form handling, validation, dependency injection, etc.).
* *Dynamic Logic*: How effectively the form adapts to user input and validation rules.
* *Database Design*: Thoughtful and efficient database structure to handle the form
  data.

# Technical overview

## Stack

1. [Symfony Docker](https://github.com/dunglas/symfony-docker) as general foundation (under the MIT License)
2. [MySQL](https://hub.docker.com/_/mysql) as database engine (as by task - "Upon successful completion,
   store the collected data in a relational database (e.g., MySQL) with a proper structure to handle each step's data.")
3. `.env` and `.env.dev` files were added to repository for presentation purposes (bad approach :D)

## Implementation

1. Form process is done via separate controller's methods and custom route for every single step to not mess up with a lot of conditions
2. Form validation:
   1. In-built symfony validators such as `Length` or `CardScheme` (advanced Symfony feature)
   2. In-built symfony field types such as `Email` or `Country`
   3. Custom validators such as `NotEmpty`, `UniqueEmail` or `CountrySpecificPostalCode`
3. Synchronisation between onboarding methods is done with SessionInterface and storing User object inside (`CacheInterface` is another option)
4. Database design:
   1. User entity to store general user information
   2. SubscriptionType entity to store types of subscription in order to easily add new ones
   3. Subscription entity to store Users' subscription using `dateStart` - `dateEnd` period method (relation to User)
   4. CreditCardInfo entity to store credit card information (relation to User)
   5. Address entity to store address information (relation to User)
5. Frontend:
   1. Twig including special functions to work with form elements and custom macros
   2. Bootstrap 5 components (including forms and cards)
6. All strings were added to translation files and ready for localization

# Getting started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to run an application
4. Run `docker compose exec php bin/console doctrine:migrations:migrate` to run database migrations
5. Open `https://localhost/onboarding` and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Run `docker compose down --remove-orphans -v` to stop containers after experience is over
