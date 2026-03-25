# Wilson

A home asset and maintenance management application for homeowners.

## About

Wilson helps you stay on top of everything in your home — from appliances and HVAC to plumbing and roofing. Track what you own, schedule recurring maintenance, log service history, and get ahead of costly replacements before they sneak up on you.

## Features

- **Asset Management** — Add and organize home assets by category (appliances, HVAC, plumbing, electrical, roofing, flooring, exterior) and location, with warranty and purchase details
- **Maintenance Scheduling** — Create recurring maintenance tasks with flexible recurrence rules; track pending and completed occurrences
- **Service Records** — Log repairs, inspections, and replacements with cost, provider, and warranty information
- **Email Reminders** — Automatic email notifications for upcoming maintenance with snooze support
- **Replacement Tracking** — Monitor asset lifespans and receive alerts when assets are approaching end-of-life
- **Asset Templates** — Reusable templates and template groups to quickly add common home assets
- **Dashboard** — At-a-glance overview of overdue tasks, upcoming maintenance, and replacement alerts
- **Two-Factor Authentication** — TOTP-based 2FA via Laravel Fortify

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 12 |
| Frontend | Livewire 4, Flux UI v2, Tailwind CSS v4, Alpine.js |
| Database | MariaDB 10.11 |
| Auth | Laravel Fortify v1 |
| Testing | Pest 4 |
| Local Dev | DDEV |
| Deployment | Laravel Cloud |

## Local Development

**Prerequisites:** [DDEV](https://ddev.readthedocs.io/en/stable/)

```bash
git clone <repo-url>
cd project-wilson
ddev start
ddev exec composer install
ddev exec php artisan key:generate
ddev exec php artisan migrate --seed
ddev exec npm install && ddev exec npm run build
```

Then visit the URL printed by `ddev start`.

## Running Tests

```bash
ddev exec php artisan test --compact
```

## Deployment

Wilson is deployed to [Laravel Cloud](https://cloud.laravel.com) via GitHub Actions. Pushing to `main` triggers an automated pipeline that lints, tests, and deploys the application.

## How This Project Was Developed

Wilson was built using [Claude Code](https://claude.ai/claude-code) — Anthropic's AI-powered CLI for software engineering. Every feature was specified in natural language and implemented iteratively through AI-assisted development, covering:

- Database schema design and Eloquent modeling
- Livewire 4 reactive component development
- UI design and styling with Flux UI and Tailwind CSS
- Pest test generation and debugging
- Deployment pipeline configuration
