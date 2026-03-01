# Feature Specification: Notification System

**Feature Branch**: `006-notification-system`
**Created**: 2026-02-28
**Status**: Draft
**Input**: User description: "Notification System: Proactive email reminders for upcoming home maintenance tasks, alerting users 30 days before maintenance is due, with digest emails, escalation reminders, snooze capability, and completed-task filtering."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Receive 30-Day Advance Maintenance Reminder (Priority: P1)

As a homeowner, I receive an email reminder 30 days before a maintenance task is due so that I have enough time to schedule and prepare for the work.

**Why this priority**: This is the core value proposition of the notification system. Without advance reminders, the entire feature has no purpose. A single reminder per task is the minimum viable notification.

**Independent Test**: Can be fully tested by creating a maintenance task with a due date 30 days in the future, running the daily notification job, and verifying an email is sent with the correct asset name, task description, due date, and link to Wilson.

**Acceptance Scenarios**:

1. **Given** a user has an active maintenance occurrence due in exactly 30 days, **When** the daily notification job runs, **Then** the user receives an email containing the asset name, task description, due date, and a link to view the task in Wilson.
2. **Given** a user has a maintenance occurrence that was already completed, **When** the daily notification job runs, **Then** no reminder email is sent for that occurrence.
3. **Given** a user has a maintenance occurrence due in 31 days, **When** the daily notification job runs, **Then** no reminder is sent (it is not yet 30 days out).
4. **Given** a user has an inactive maintenance task, **When** the daily notification job runs, **Then** no reminder is sent for any of its occurrences.

---

### User Story 2 - Receive Digest Email for Multiple Upcoming Tasks (Priority: P2)

As a homeowner with multiple assets, I receive a single consolidated digest email when several maintenance tasks are approaching their due dates rather than being overwhelmed with individual emails.

**Why this priority**: Users with many assets could receive dozens of individual emails. Consolidation is essential for usability and prevents notification fatigue.

**Independent Test**: Can be tested by creating multiple maintenance occurrences for the same user all due within the notification window, running the daily job, and verifying a single digest email is sent listing all upcoming tasks.

**Acceptance Scenarios**:

1. **Given** a user has 3 maintenance occurrences triggering notifications on the same day, **When** the daily notification job runs, **Then** the user receives one digest email listing all 3 tasks with their respective asset names, descriptions, and due dates.
2. **Given** a user has only 1 maintenance occurrence triggering a notification, **When** the daily notification job runs, **Then** the user receives a single-task email (not a digest format).

---

### User Story 3 - Recurring Task Notification Cycling (Priority: P2)

As a homeowner with recurring maintenance tasks (e.g., quarterly filter changes), I receive reminders for each upcoming occurrence without manual intervention.

**Why this priority**: Recurring tasks are a core use case for home maintenance. The system must properly calculate and notify for the next pending occurrence in a recurring series.

**Independent Test**: Can be tested by creating a recurring monthly task, completing the current occurrence, verifying a new occurrence is generated, and confirming a 30-day reminder is scheduled for the new due date.

**Acceptance Scenarios**:

1. **Given** a user has a recurring quarterly maintenance task and the current occurrence is completed, **When** the next occurrence's due date is 30 days away, **Then** the user receives a reminder for the next occurrence.
2. **Given** a user has a recurring task but the task is marked inactive, **When** the next occurrence's due date approaches, **Then** no reminder is sent.

---

### User Story 4 - Receive Escalation Reminders as Due Date Approaches (Priority: P3)

As a homeowner, I receive follow-up reminders at 7 days and 1 day before a task is due so that urgent maintenance doesn't slip through the cracks.

**Why this priority**: Escalation reminders add urgency as due dates approach. This builds on the 30-day reminder and increases the likelihood of task completion.

**Independent Test**: Can be tested by creating a maintenance occurrence due in 7 days (and separately 1 day), running the daily job, and verifying escalation emails are sent with appropriate urgency messaging.

**Acceptance Scenarios**:

1. **Given** a user has an uncompleted maintenance occurrence due in 7 days, **When** the daily notification job runs, **Then** the user receives a 7-day reminder email with increased urgency messaging.
2. **Given** a user has an uncompleted maintenance occurrence due in 1 day, **When** the daily notification job runs, **Then** the user receives a 1-day reminder email with high urgency messaging.
3. **Given** a user already completed the maintenance occurrence, **When** the 7-day or 1-day checkpoint arrives, **Then** no escalation reminder is sent.
4. **Given** a user received a 30-day reminder and has not completed the task, **When** the 7-day checkpoint arrives, **Then** the escalation email references the approaching deadline without repeating the full 30-day content.

---

### User Story 5 - Snooze/Postpone a Notification (Priority: P4)

As a homeowner, I can snooze or postpone a reminder from within the email so that I can reschedule the notification if I'm not ready to act on it yet.

**Why this priority**: Snoozing provides flexibility and prevents users from ignoring reminders they can't act on immediately. This improves engagement over time.

**Independent Test**: Can be tested by clicking a snooze link in a reminder email, verifying the notification is rescheduled, and confirming the rescheduled reminder arrives at the new time.

**Acceptance Scenarios**:

1. **Given** a user receives a maintenance reminder email, **When** they click the "Snooze 7 days" link, **Then** the reminder is suppressed and a new reminder is scheduled for 7 days later.
2. **Given** a user receives a maintenance reminder email, **When** they click the "Snooze 3 days" link, **Then** the reminder is suppressed and a new reminder is scheduled for 3 days later.
3. **Given** a user has snoozed a reminder and the snooze period has elapsed, **When** the daily notification job runs, **Then** the user receives the rescheduled reminder.
4. **Given** a user snoozes a reminder past the task's due date, **When** the snooze period elapses, **Then** no reminder is sent (the task is now overdue, not upcoming).

---

### Edge Cases

- What happens when a maintenance due date is less than 30 days from today when the task is first created? The system sends the first applicable reminder (7-day or 1-day) rather than the missed 30-day reminder.
- How does the system handle a user with no verified email address? The system skips notification for users without a verified email address.
- What happens if the daily job fails to run for a day? Missed notifications are sent on the next successful run (the job checks for all unsent notifications up to and including today).
- How does the system handle February 29 (leap year) due dates? Standard date arithmetic is used; a task due on Feb 29 in a non-leap year is treated as Feb 28.
- What happens when a user snoozes multiple times? Each snooze replaces the previous snooze schedule. There is no limit on snooze count, but snoozing past the due date cancels the reminder.
- What if two occurrences for the same task fall within the same notification window? Each occurrence is treated independently and included in the digest.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST send email reminders 30 days before a maintenance occurrence due date.
- **FR-002**: System MUST run a daily scheduled job to identify and send pending notifications.
- **FR-003**: System MUST skip notifications for completed maintenance occurrences (where the occurrence is marked complete).
- **FR-004**: System MUST skip notifications for inactive maintenance tasks.
- **FR-005**: System MUST consolidate multiple notifications for the same user into a single digest email when more than one notification is triggered on the same day.
- **FR-006**: System MUST send a single-task email (not digest) when only one notification is triggered for a user.
- **FR-007**: System MUST send escalation reminders at 7 days and 1 day before the due date for uncompleted occurrences.
- **FR-008**: System MUST not send duplicate notifications for the same occurrence at the same reminder interval (30-day, 7-day, 1-day).
- **FR-009**: System MUST include in each reminder email: asset name, maintenance task description, due date, and a direct link to the task in Wilson.
- **FR-010**: System MUST allow users to snooze a reminder via a link in the email, with options for 3-day and 7-day snooze periods.
- **FR-011**: System MUST suppress the current reminder and schedule a new one when a user snoozes.
- **FR-012**: System MUST not send a snoozed reminder if the snooze date falls on or after the task's due date.
- **FR-013**: System MUST only send notifications to users with verified email addresses.
- **FR-014**: System MUST handle recurring maintenance tasks by sending reminders for the next pending occurrence in the series.
- **FR-015**: System MUST catch up on any missed notifications if the daily job was not executed on a prior day.
- **FR-016**: Email templates MUST be clean and branded, clearly communicating the maintenance need and urgency level (standard, approaching, urgent).

### Key Entities

- **Notification Record**: Tracks which reminders have been sent for which maintenance occurrence at which interval (30-day, 7-day, 1-day). Prevents duplicate sends. Links to the maintenance occurrence and the user.
- **Snooze Record**: Tracks user snooze actions including the original notification, snooze duration selected, and the rescheduled send date. Associated with a notification record.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users receive maintenance reminders at least 30 days before task due dates with 100% reliability for active, uncompleted tasks.
- **SC-002**: Users with multiple upcoming tasks receive a single consolidated digest email rather than individual emails.
- **SC-003**: Escalation reminders are sent at 7-day and 1-day intervals for tasks that remain uncompleted.
- **SC-004**: Users can snooze reminders and receive rescheduled notifications at the selected interval.
- **SC-005**: No duplicate reminders are sent for the same task at the same interval.
- **SC-006**: Completed tasks never generate reminder emails.
- **SC-007**: The daily notification job processes all pending notifications within 5 minutes for up to 10,000 users.
- **SC-008**: Each reminder email contains all required context (asset, task, due date, link) so the user can act without additional navigation.

## Assumptions

- The application already has a working email configuration (Mailgun via Laravel's mail system).
- Maintenance occurrences with due dates and completion status are the source of truth for notification scheduling.
- The maintenance task's active flag determines whether a task should generate notifications.
- Users must have a verified email to receive notifications.
- Snooze functionality is accessed via secure signed links in the email, not requiring the user to log in.
- The daily job runs via the application's task scheduler (typically via cron).
- "Branded" email templates follow the existing application's visual identity.
- A task due in fewer than 30 days from creation will receive the next applicable reminder (7-day or 1-day) rather than a retroactive 30-day notice.
