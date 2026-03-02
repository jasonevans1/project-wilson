# Quickstart: Notification System

**Branch**: `006-notification-system` | **Date**: 2026-02-28

## Prerequisites

- Existing models: `User`, `Asset`, `MaintenanceTask`, `MaintenanceOccurrence`
- Database queue driver configured (already in place)
- Mail configuration (currently `log` driver — switch to Mailgun for production)

## New Files to Create

### Models & Database

| File | Command | Purpose |
|------|---------|---------|
| `app/Models/MaintenanceReminder.php` | `php artisan make:model MaintenanceReminder -mf --no-interaction` | Reminder tracking with factory |
| `app/Enums/ReminderType.php` | Manual (enum) | 30_day, 7_day, 1_day enum |
| Migration | Created with model | `maintenance_reminders` table |

### Notifications

| File | Purpose |
|------|---------|
| `app/Notifications/MaintenanceReminderNotification.php` | Single-task reminder email |
| `app/Notifications/MaintenanceDigestNotification.php` | Multi-task digest email |

### Commands & Jobs

| File | Command | Purpose |
|------|---------|---------|
| `app/Console/Commands/SendMaintenanceReminders.php` | `php artisan make:command SendMaintenanceReminders --no-interaction` | Daily scheduled command |

### Controllers

| File | Command | Purpose |
|------|---------|---------|
| `app/Http/Controllers/ReminderSnoozeController.php` | `php artisan make:controller ReminderSnoozeController --no-interaction` | Handle snooze signed URLs |

### Views

| File | Purpose |
|------|---------|
| `resources/views/mail/maintenance-reminder.blade.php` | Single reminder email template |
| `resources/views/mail/maintenance-digest.blade.php` | Digest email template |
| `resources/views/reminders/snoozed.blade.php` | Snooze confirmation page |

### Routes

| File | Addition |
|------|----------|
| `routes/web.php` | Include `routes/reminders.php` |
| `routes/reminders.php` | Snooze signed route + confirmation page |
| `routes/console.php` | Schedule `maintenance:send-reminders` daily |

### Tests

| File | Command | Coverage |
|------|---------|----------|
| `tests/Feature/SendMaintenanceRemindersTest.php` | `php artisan make:test SendMaintenanceRemindersTest --pest --no-interaction` | Daily job logic, dedup, catch-up |
| `tests/Feature/ReminderSnoozeTest.php` | `php artisan make:test ReminderSnoozeTest --pest --no-interaction` | Snooze signed URL handling |
| `tests/Feature/MaintenanceReminderNotificationTest.php` | `php artisan make:test MaintenanceReminderNotificationTest --pest --no-interaction` | Email content, urgency levels |
| `tests/Feature/MaintenanceDigestNotificationTest.php` | `php artisan make:test MaintenanceDigestNotificationTest --pest --no-interaction` | Digest email grouping |
| `tests/Unit/ReminderTypeTest.php` | `php artisan make:test ReminderTypeTest --pest --unit --no-interaction` | Enum values and labels |

## Implementation Order

1. **ReminderType enum** + **MaintenanceReminder model** with migration and factory
2. **SendMaintenanceReminders command** — core business logic
3. **MaintenanceReminderNotification** — single-task email
4. **MaintenanceDigestNotification** — multi-task email
5. **Schedule registration** in `routes/console.php`
6. **ReminderSnoozeController** + routes + signed URL generation
7. **Email templates** (Blade views)
8. **Snooze confirmation page**

## Environment Configuration Needed

```env
# Mail (switch from log to Mailgun for production)
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-api-key

# App URL (needed for signed URLs in emails)
APP_URL=https://your-wilson-domain.com
```

## Key Patterns to Follow

- **Eager loading**: When querying occurrences for reminders, always `->with(['task.asset', 'task.user'])`
- **Queued notifications**: Both notification classes implement `ShouldQueue`
- **Signed URLs**: Use `URL::signedRoute('maintenance.reminder.snooze', [...])` for snooze links
- **Named routes**: All new routes must have names matching the `maintenance.reminder.*` convention
- **Factory states**: Create factory states for `sent`, `snoozed`, `pending` reminders
