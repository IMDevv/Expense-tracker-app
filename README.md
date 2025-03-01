# Expense Tracker Application

<p align="center">
<img src="dashboard.png" width="600" alt="Expense Tracker Dashboard">
</p>

A modern expense tracking application built with Laravel 12, Livewire, and Tailwind CSS. Track expenses, manage budgets, and get insights into spending habits.

## Screenshots

<details>
<summary>View screenshots</summary>

### Dashboard
![Dashboard](screenshots/dashboard.png)

### Budget Management
![Budgets](screenshots/budgets.png)

### Expenses
![Reports](screenshots/expenses.png)

### Reports
![Reports](screenshots/reports.png)

</details>

## Features

- 📊 Dashboard with expense overview
- 💰 Budget management
- 📝 Expense tracking
- 📈 Reports and analytics
- 📧 Email notifications
- 🌓 Dark mode support
- 👤 User profile management
- 📱 Responsive design

## Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- PostgreSQL
- Gmail account (for email notifications)

## Installation

1. Clone the repository
2. Install PHP dependencies (composer install)
3. Install Node.js dependencies (npm install && npm run dev)
4. Create environment file (cp .env.example .env)
5. Generate application key (php artisan key:generate)
6. Configure your database in `.env` 
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=expense_tracker
    DB_USERNAME=set_database_username
    DB_PASSWORD=set_database_password
7. Configure email settings in `.env`
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=set_gmail_email
    MAIL_PASSWORD=set_gmail_app_password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=set_gmail_email
8. Run migrations (php artisan migrate)
9. Run seeders (php artisan db:seed)
10. Create storage link (php artisan storage:link)
11. Run the development server (php artisan serve)

## Email Configuration

To enable email notifications:

1. Enable 2-Step Verification in your Google Account
2. Generate an App Password:
   - Go to Google Account Security settings
   - Select "App passwords"
   - Generate a new app password
   - Use this password in your .env file

## Testing

Run the test suite:
```
php artisan test
```
