# Research: Notification System

**Branch**: `006-notification-system` | **Date**: 2026-02-28

## R-001: Notification Delivery Mechanism

**Decision**: Use Laravel's `Notification` system with the `mail` channel, not raw `Mailable` classes.

**Rationale**: The User model already uses the `Notifiable` trait. Laravel Notifications provide built-in support for: channel routing (mail, database, etc.), queuing via `ShouldQueue`, deduplication logic, and a clean separation between notification content and delivery. This aligns with the constitution's principle of using framework conventions.

**Alternatives considered**:
- Raw `Mailable` classes — more boilerplate, no built-in notification history, harder to extend to other channels later.
- Third-party notification packages — unnecessary given Laravel's built-in support.

## R-002: Duplicate Prevention Strategy

**Decision**: Use a dedicated `maintenance_reminders` database table to track sent reminders per occurrence + interval combination.

**Rationale**: The daily job must be idempotent. Tracking `(maintenance_occurrence_id, reminder_type)` with a unique constraint ensures duplicates are impossible even if the job runs multiple times per day or catches up after downtime. This is simpler and more reliable than checking mail logs.

**Alternatives considered**:
- Check sent mail logs — unreliable, mail could be queued but not yet sent.
- Cache-based tracking — volatile, lost on restart.
- Database `notifications` table (Laravel built-in) — possible but overloaded for this purpose; a dedicated table provides clearer queries and indexing.

## R-003: Digest vs. Individual Email Strategy

**Decision**: Collect all pending reminders per user per day, then dispatch either a single-task notification or a digest notification based on count.

**Rationale**: The daily job groups reminders by user. If count == 1, send a single-task email. If count > 1, send a digest email. Two separate notification classes (`MaintenanceReminderNotification` and `MaintenanceDigestNotification`) keep templates focused and testable.

**Alternatives considered**:
- Single notification class with conditional template — messy blade logic, harder to test.
- Always use digest format even for single tasks — poor UX, overly complex for one item.

## R-004: Snooze Implementation via Signed URLs

**Decision**: Use Laravel's signed URL feature (`URL::signedRoute()`) to generate snooze links in emails. A dedicated controller handles the signed route, validates the signature, and creates/updates the snooze record.

**Rationale**: Signed URLs are secure (tamper-proof), don't require authentication, and are a standard Laravel pattern for email action links. The constitution requires using `route()` for URL generation, and signed routes follow this pattern. No login required — critical for email UX.

**Alternatives considered**:
- Require login to snooze — poor UX from email context.
- Token-based auth — reinventing what signed URLs already provide.

## R-005: Scheduled Job Architecture

**Decision**: Register a single daily scheduled command in `routes/console.php` that dispatches a queued job (`SendMaintenanceReminders`). The job handles the business logic of finding pending notifications, grouping by user, and dispatching notification classes.

**Rationale**: The constitution requires `ShouldQueue` for I/O-heavy operations. Separating the scheduler entry (thin — just dispatches) from the job (thick — contains logic) follows single-responsibility. The existing database queue driver is already configured with `jobs` and `failed_jobs` tables.

**Alternatives considered**:
- Inline all logic in the scheduled command — violates separation of concerns, not retryable.
- Multiple specialized jobs per reminder type — over-engineering for the current scope.

## R-006: Escalation Reminder Approach

**Decision**: Treat escalation reminders (7-day, 1-day) as additional `reminder_type` values on the same `maintenance_reminders` table. The daily job checks for all three intervals: 30-day, 7-day, and 1-day.

**Rationale**: Using the same tracking mechanism for all reminder types simplifies the code. The unique constraint `(maintenance_occurrence_id, reminder_type)` prevents duplicates at each tier. Escalation emails use the same notification classes but with urgency-level context.

**Alternatives considered**:
- Separate tables per escalation level — unnecessary complexity.
- Separate jobs per escalation level — over-engineering.

## R-007: Email Template Approach

**Decision**: Use Laravel Markdown mail notifications (`MailMessage` with markdown templates) for branded, responsive emails.

**Rationale**: Laravel's markdown mail provides a pre-built responsive layout that can be customized via `php artisan vendor:publish --tag=laravel-mail`. This gives us branded templates without building from scratch. The markdown system supports components like tables, buttons, and panels — ideal for maintenance reminder content.

**Alternatives considered**:
- Custom Blade HTML templates — more work, must handle email client compatibility manually.
- Plain text emails — poor UX, no branding, no action buttons.

## R-008: Catch-Up Logic for Missed Days

**Decision**: The daily job queries for all unsent reminders where the notification date is <= today (not just == today). This naturally handles catch-up without special logic.

**Rationale**: If the job didn't run yesterday, today's run finds both yesterday's and today's pending reminders. The `<=` comparison plus the unique constraint makes this idempotent. No separate catch-up mechanism needed.

**Alternatives considered**:
- Track last-run date and replay missed days — unnecessary complexity.
- Skip missed notifications — violates FR-015.
