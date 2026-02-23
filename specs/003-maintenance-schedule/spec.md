# Feature Specification: Maintenance Schedule & Task Management

**Feature Branch**: `003-maintenance-schedule`
**Created**: 2026-02-19
**Status**: Draft
**Input**: User description: "The next task is to build the Maintenance Schedule & Task Management for their home assets. Users can create a Recurring maintenance task. This will include task due date calculations. Task completion tracking. Manual schedule adjustment will be supported."

## Clarifications

### Session 2026-02-19

- Q: Should recurrence be fixed intervals (daily/weekly/monthly/quarterly/yearly) only, or interval unit + multiplier (e.g., every 2 weeks)? → A: Interval unit + multiplier — user selects a unit (daily/weekly/monthly/yearly) and enters a count (e.g., every 2 weeks, every 3 months).
- Q: Should the system maintain one pending occurrence per task at a time, or pre-generate a rolling window of multiple future occurrences? → A: One at a time — exactly one pending occurrence per task; the next is generated upon completion of the current one.
- Q: Can users delete or deactivate a MaintenanceTask, and what happens to its occurrences? → A: Soft delete only — users can deactivate a task, removing it from the active schedule while preserving its completed occurrence history.
- Q: Are assets and maintenance tasks private per user, or shared across household members? → A: Strictly private — each user can only view and manage their own assets and maintenance tasks.
- Q: How should overdue tasks be visually distinguished in the schedule — inline badge/color or a separate section? → A: Inline badge/color — overdue tasks appear in the same date-sorted list with a visible "Overdue" badge or distinct color treatment.

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Create a Recurring Maintenance Task (Priority: P1)

A homeowner selects one of their tracked assets and creates a recurring maintenance task for it. They give the task a name (e.g., "Change HVAC filter"), set how often it recurs (e.g., every 3 months), and optionally provide a description and a starting due date. After saving, the system automatically computes and schedules the first due occurrence.

**Why this priority**: This is the foundational capability — without the ability to create tasks, no other functionality is possible. Delivering this alone already provides value by letting users define what needs maintaining and when.

**Independent Test**: Can be fully tested by navigating to an asset, creating a recurring task with an interval, and confirming the first due date appears correctly on the schedule.

**Acceptance Scenarios**:

1. **Given** a user is viewing an asset, **When** they create a recurring task with a name and a monthly recurrence, **Then** the task is saved and the next due date is calculated as one month from the start date.
2. **Given** a user creates a task without providing a start date, **When** the task is saved, **Then** the first due date defaults to today's date.
3. **Given** a user attempts to save a task without a name, **When** they submit the form, **Then** a validation error is shown and the task is not saved.

---

### User Story 2 — View Maintenance Schedule (Priority: P2)

A homeowner views a consolidated schedule of all upcoming and overdue maintenance tasks across all their assets. Tasks are sorted by due date, with overdue tasks visually distinguished. The user can also filter the schedule to see tasks for a single asset.

**Why this priority**: The schedule view transforms the data into actionable information. Without it, users have no way to know what is coming up or overdue.

**Independent Test**: Can be fully tested with existing tasks in the system by navigating to the maintenance schedule page and confirming tasks appear sorted by due date with overdue tasks highlighted.

**Acceptance Scenarios**:

1. **Given** a user has multiple assets with maintenance tasks, **When** they visit the maintenance schedule, **Then** all upcoming tasks from all assets are listed in ascending order by due date.
2. **Given** a task's due date has passed and it is not yet completed, **When** the schedule is viewed, **Then** that task is clearly marked as overdue.
3. **Given** a user wants to see tasks for a specific asset, **When** they filter by that asset, **Then** only tasks belonging to that asset are shown.

---

### User Story 3 — Mark a Task as Complete (Priority: P2)

A homeowner marks an overdue or upcoming task as complete. The system records the completion date and automatically generates the next occurrence of that task based on the recurrence interval.

**Why this priority**: Task completion is the core interaction loop. It allows users to maintain their history and ensures the schedule stays up to date without manual re-scheduling.

**Independent Test**: Can be fully tested by marking any scheduled task as complete and confirming a new occurrence appears in the schedule with the correctly calculated future due date.

**Acceptance Scenarios**:

1. **Given** a user has a monthly recurring task due today, **When** they mark it as complete, **Then** the task is recorded as completed on today's date and a new occurrence appears one month from today.
2. **Given** a user completes a task before its due date, **When** the next occurrence is generated, **Then** the next due date is calculated from the original due date (not the early completion date), preserving the intended schedule.
3. **Given** a completed task, **When** the user views the asset's task history, **Then** the completed occurrence is visible with its completion date.

---

### User Story 4 — Manually Adjust a Task's Due Date (Priority: P3)

A homeowner needs to postpone or bring forward a scheduled maintenance task. They edit the due date of a specific scheduled occurrence without changing the underlying recurrence rule. Future occurrences after the adjusted one continue from the new due date.

**Why this priority**: Real life is unpredictable. Users need flexibility to shift individual occurrences without invalidating the whole recurring schedule.

**Independent Test**: Can be fully tested by editing a scheduled occurrence's due date and confirming future occurrences regenerate from the adjusted date while past occurrences remain unchanged.

**Acceptance Scenarios**:

1. **Given** an upcoming task due in 2 weeks, **When** the user adjusts the due date to 4 weeks from now, **Then** that occurrence's due date is updated and the next occurrence after completion will be calculated from the adjusted date.
2. **Given** a user edits a due date, **When** they save the change, **Then** only the selected occurrence is affected; other existing occurrences are unchanged.
3. **Given** a user tries to set a due date in the past on an incomplete task, **When** they save, **Then** the task is saved and displayed as overdue immediately.

---

### Edge Cases

- What happens when a user deletes an asset that has active maintenance tasks? (The tasks and their history should remain visible or be soft-deleted along with the asset.)
- How does the system handle a recurrence interval adjustment on an existing task (e.g., changing from monthly to quarterly)? (Future unstarted occurrences are recalculated; completed occurrences are preserved.)
- What if no tasks exist for any asset? (The schedule view should display a friendly empty state with a prompt to create the first task.)
- What happens when completing a task on a non-recurring task? (A non-recurring task is simply marked complete with no follow-up occurrence generated.)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Users MUST be able to create a maintenance task linked to a specific home asset, providing at minimum a task name and recurrence interval.
- **FR-002**: System MUST support a recurrence model of interval unit + multiplier count. Supported units are: daily, weekly, monthly, and yearly. Users specify how many units between occurrences (e.g., every 2 weeks, every 3 months). "Quarterly" is expressed as monthly × 3.
- **FR-003**: System MUST automatically calculate and create exactly one pending occurrence when a recurring task is saved, defaulting the start date to today if none is provided. At any point in time, each task has exactly one pending (incomplete) occurrence.
- **FR-004**: Users MUST be able to view a consolidated maintenance schedule listing all upcoming and overdue task occurrences across all their assets.
- **FR-005**: System MUST distinguish overdue task occurrences from upcoming ones in the schedule view using an inline visual treatment (e.g., a colored "Overdue" badge). All occurrences remain in a single date-sorted list.
- **FR-006**: Users MUST be able to mark a specific task occurrence as complete, recording the completion date.
- **FR-007**: System MUST automatically generate the next scheduled occurrence upon completion of a recurring task, calculating the due date from the prior occurrence's due date (not the completion date).
- **FR-008**: Users MUST be able to manually edit the due date of any individual scheduled task occurrence.
- **FR-009**: Users MUST be able to view all maintenance tasks and their history for a specific asset.
- **FR-010**: Users MUST be able to add an optional description or notes to a maintenance task.
- **FR-011**: System MUST allow users to filter or view the maintenance schedule scoped to a single asset.
- **FR-012**: Users MUST be able to deactivate a MaintenanceTask. A deactivated task is hidden from the active schedule and no new occurrences are generated, but all completed occurrence history is preserved and remains viewable.

### Key Entities

- **MaintenanceTask**: A recurring task definition linked to an asset. Attributes: name, description/notes, recurrence unit (daily/weekly/monthly/yearly), recurrence count (positive integer, e.g., 2 for "every 2 weeks"), active status (true = active and scheduled; false = deactivated, hidden from schedule, history preserved).
- **MaintenanceOccurrence**: A specific scheduled instance of a MaintenanceTask. Attributes: due date, completion date, completed status, notes for this occurrence.
- **Asset**: An existing entity representing a home asset — maintenance tasks belong to an asset.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can create a recurring maintenance task for an asset in under 2 minutes from start to confirmation.
- **SC-002**: The maintenance schedule view correctly lists all upcoming and overdue occurrences in a single list sorted by due date ascending. Overdue items display a visible inline "Overdue" badge or equivalent color indicator.
- **SC-003**: When a task occurrence is marked complete, the next scheduled occurrence appears in the schedule within the same interaction (no page reload required).
- **SC-004**: When a user manually adjusts a due date, the change is reflected immediately in the schedule view.
- **SC-005**: 100% of recurring tasks produce the correct next due date upon completion, calculated from the prior occurrence's due date.
- **SC-006**: The schedule and task history are accessible per-asset and system-wide without data loss or confusion between assets.

## Assumptions

- Users are authenticated homeowners who already have assets defined in the system (the asset management feature from 001-home-asset-crud and the template system from 002-asset-template-system are prerequisites). All assets and maintenance tasks are strictly private to the owning user — no cross-user visibility or sharing.
- Recurrence is defined by a unit (daily, weekly, monthly, yearly) plus a positive integer count (e.g., unit=weekly, count=2 means "every 2 weeks"). This keeps date calculations predictable and testable without a fully free-form interval.
- The next occurrence due date is calculated from the original scheduled due date (not the actual completion date) to preserve schedule integrity — e.g., completing a monthly task 3 days early does not shift the entire future schedule earlier.
- Non-recurring (one-off) tasks are out of scope for this feature; all tasks created here are recurring.
- Email/push notification reminders for upcoming tasks are out of scope for this iteration.
- Mobile-specific optimizations are out of scope; the feature targets the existing web interface.
