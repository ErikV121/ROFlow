# ROFlow
A maintenance ticketing and repair order system built in PHP. This is a learning project 
to get hands on with the MVC architecture (PHP, PostgreSQL, jQuery, HTML, CSS, JavaScript) without leaning on a 
framework like Laravel.

## Demos

Feel free to check out the demos online [here](https://roflow.azurewebsites.net/login) or watch the videos below for a quick walkthrough of the core features and workflows.

Use the following credentials to log in and explore the system or create your own account:
### Advisors credentials:
- username: John_Doe
- password: password
### Technicians credentials:
- username: R_Roe
- password: password

### 1. Register & Login
Staff account creation and role-based routing (Advisor vs Technician).

![Register & Login Demo](demo/register.mp4)

### 2. Advisor Workflow (Part 1: Intake)
Taking a customer's vehicle info, creating an Intake RO, and assigning it to a technician.

![Advisor Intake Demo](demo/advisor_1.mp4)

### 3. Technician Workflow
Starting an assigned inspection, logging issues, and submitting findings back to the Advisor.

![Technician Demo](demo/tech_1.mp4)

### 4. Advisor Workflow (Part 2: Review)
Reviewing the technician's inspection findings, adding prices, and generating a customer portal link.

![Advisor Review Demo](demo/advisor_2.mp4)

## Stack
- PHP 8+
- PostgreSQL
- jQuery 3+ & JavaScript
- HTML / CSS
- XAMPP (for local development)

## Features
- User authentication & Roles (Advisors & Technicians)
- Ticket (Repair Order) CRUD operations
- Role specific Dashboards
- Technician Inspection Forms
- Customer Approval Portal

## Architecture Notes
- **Service/Repository Pattern**: Controllers handle requests, Services manage business logic, and Repositories handle pure SQL with PDO through Dependency Injection.
- **Front Controller**: public/index.php processes all routing; src/ forms the application core and is kept strictly off limits from the web.
- **Configuration**: Uses config/LocalConfig.php locally and natively supports Docker Environment variables.


## Status
Learning project actively being built out to mock a real auto shop. Not production-ready.
