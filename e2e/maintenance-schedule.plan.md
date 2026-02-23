# Maintenance Schedule — Playwright E2E Test Plan

## Application Overview

End-to-end Playwright tests for the Maintenance Schedule feature of the Wilson application (Laravel 12 + Livewire 4 + Flux UI Free v2).

The feature allows authenticated users to:
- Create recurring maintenance tasks per asset on /assets/{asset}/maintenance
- View a consolidated pending-occurrence schedule sorted by due date on /maintenance
- Mark individual occurrences complete (auto-generates next occurrence)
- Edit occurrence due dates inline with a pencil icon on the per-asset view
- Deactivate tasks (preserves history, removes from all views)

All routes require auth + verified middleware. Interactions are handled reactively by Livewire with no full page reloads. The deactivate action uses a browser-native confirm() dialog (wire:confirm).

The existing Pest feature test suite already covers component-level business logic, validation rules, authorization, DB state, and next-occurrence calculation. These Playwright tests focus exclusively on browser-level interactions: navigating pages, filling forms, observing reactive DOM changes, and confirming visual feedback.

Conventions from the existing e2e suite:
- Tests import from ./fixtures which provides registerAndGoToAssets (creates a fresh user) and loginAndGoToAssets helpers
- Each test creates its own isolated user via registerAndGoToAssets so tests are independent and repeatable
- Spec files live in e2e/ directory, matching the testDir: ./e2e config in playwright.config.ts
- A shared maintenance-fixtures.ts helper should provide a loginAndGoToMaintenance and createMaintenanceTask helper for reuse across the suite

## Test Scenarios

### 1. Authentication Guard

**Seed:** `e2e/seed.spec.ts`

#### 1.1. Unauthenticated user visiting /maintenance is redirected to the login page

**File:** `e2e/maintenance-auth-guard.spec.ts`

**Steps:**
  1. Open a fresh browser context with no existing session and navigate directly to /maintenance
    - expect: The browser is redirected to /login
    - expect: The URL is https://project-wilson.ddev.site/login
    - expect: The login form is visible with Email address and Password fields
  2. Observe the page heading
    - expect: The heading 'Log in to your account' is visible

#### 1.2. Unauthenticated user visiting /assets/1/maintenance is redirected to the login page

**File:** `e2e/maintenance-auth-guard.spec.ts`

**Steps:**
  1. Open a fresh browser context with no existing session and navigate directly to /assets/1/maintenance
    - expect: The browser is redirected to /login
    - expect: The URL is https://project-wilson.ddev.site/login

#### 1.3. Authenticated user can reach /maintenance via the sidebar Maintenance nav link

**File:** `e2e/maintenance-auth-guard.spec.ts`

**Steps:**
  1. Register a new user via registerAndGoToAssets and then navigate to the dashboard
    - expect: The authenticated dashboard is visible with the sidebar navigation showing Dashboard, Assets, and Maintenance links
  2. Click the 'Maintenance' link in the sidebar navigation
    - expect: The browser navigates to /maintenance
    - expect: The page heading 'Maintenance Schedule' is visible
    - expect: The asset filter dropdown is visible on the page

### 2. US1 — Create a Maintenance Task

**Seed:** `e2e/seed.spec.ts`

#### 2.1. Happy path: create a monthly task with required fields only, form resets and card appears

**File:** `e2e/maintenance-create-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset (name: 'Test HVAC', category: 'hvac', location: 'Basement'), then navigate to that asset's maintenance page at /assets/{id}/maintenance
    - expect: The page heading 'Test HVAC — Maintenance Tasks' is visible
    - expect: The 'Add Maintenance Task' form card is rendered with Task Name, Description, Recurrence dropdown, Every (count), and Start Date fields
    - expect: The empty state text 'No maintenance tasks yet.' is visible
  2. Fill the Task Name field with 'Replace Air Filter'. Leave Description empty, leave Recurrence at the default Monthly, leave Every (count) at 1, and leave Start Date empty
    - expect: The Task Name field contains 'Replace Air Filter'
  3. Click the 'Add Task' button
    - expect: The form fields reset: Task Name clears, Every (count) returns to 1, Recurrence returns to Monthly
    - expect: The empty state text disappears
    - expect: A new task card for 'Replace Air Filter' appears in the task list below the form without a full page reload
    - expect: The task card shows 'Every 1 Monthly'
    - expect: The task card shows a due date in the format 'Due: Mon D, YYYY' using today's date
    - expect: A 'Mark Complete' button is visible on the task card
    - expect: A 'Deactivate' button is visible on the task card

#### 2.2. Happy path: create a yearly task with all fields filled including description and future start date

**File:** `e2e/maintenance-create-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, and navigate to that asset's maintenance page
    - expect: The 'Add Maintenance Task' form is visible
  2. Fill Task Name with 'Annual Boiler Inspection', fill Description with 'Full system check by certified technician', select 'Yearly' from the Recurrence dropdown, set Every (count) to 2, and set Start Date to 2027-03-15
  3. Click 'Add Task'
    - expect: A task card for 'Annual Boiler Inspection' appears in the task list
    - expect: The card shows 'Every 2 Yearly'
    - expect: The card shows 'Due: Mar 15, 2027'

#### 2.3. Recurrence dropdown contains Daily, Weekly, Monthly, and Yearly options with Monthly as default

**File:** `e2e/maintenance-create-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, and navigate to the asset's maintenance page
    - expect: The 'Add Maintenance Task' form is visible
  2. Inspect the Recurrence combobox options
    - expect: The dropdown contains exactly four options: Daily, Weekly, Monthly, Yearly
    - expect: Monthly is the selected default option

#### 2.4. Validation: submitting with an empty Task Name shows an error and does not create a task

**File:** `e2e/maintenance-create-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, and navigate to the asset's maintenance page
    - expect: The 'Add Maintenance Task' form is visible
  2. Leave the Task Name field empty and click 'Add Task'
    - expect: No new task card is added to the task list
    - expect: The Task Name field shows a validation error state (red border, data-invalid attribute, or an error message rendered near the field)
    - expect: The form remains visible and open

#### 2.5. Validation: submitting with recurrence count of 0 shows an error and does not create a task

**File:** `e2e/maintenance-create-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, and navigate to the asset's maintenance page
    - expect: The 'Add Maintenance Task' form is visible
  2. Fill Task Name with 'Filter Replacement', clear Every (count) and type 0, then click 'Add Task'
    - expect: No new task card is added to the task list
    - expect: The Every (count) field shows a validation error state
    - expect: The form remains visible and open

#### 2.6. A newly created task's occurrence appears on the consolidated /maintenance schedule

**File:** `e2e/maintenance-create-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset named 'My HVAC Unit', and navigate to that asset's maintenance page
    - expect: The maintenance task form is visible
  2. Create a monthly task named 'Schedule Visibility Check' (default settings) and click 'Add Task'
    - expect: The 'Schedule Visibility Check' card appears in the per-asset task list
  3. Click the 'Maintenance' link in the sidebar to navigate to /maintenance
    - expect: An occurrence card for 'Schedule Visibility Check' is visible on the consolidated schedule
    - expect: The card shows the asset name 'My HVAC Unit'
    - expect: The card shows a due date

### 3. US2 — View Maintenance Schedule

**Seed:** `e2e/seed.spec.ts`

#### 3.1. Schedule shows task name, asset name, and formatted due date on each occurrence card

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create one asset named 'Boiler Room Unit', navigate to its maintenance page, and create a task named 'Check Pressure Valve' (Monthly, count 1, no start date)
    - expect: The task card appears in the per-asset list
  2. Navigate to /maintenance via the sidebar
    - expect: An occurrence card for 'Check Pressure Valve' is visible
    - expect: The card displays the task name 'Check Pressure Valve'
    - expect: The card displays the asset name 'Boiler Room Unit'
    - expect: The card displays a due date label in the format 'Due: Mon D, YYYY'

#### 3.2. Asset filter dropdown lists all user assets plus 'All Assets'

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create two assets ('Unit Alpha' and 'Unit Beta'), create one task on each, then navigate to /maintenance
    - expect: The asset filter combobox is visible on the page
  2. Inspect the asset filter combobox options
    - expect: The dropdown contains an 'All Assets' option
    - expect: The dropdown contains 'Unit Alpha'
    - expect: The dropdown contains 'Unit Beta'

#### 3.3. Selecting an asset in the filter shows only occurrences for that asset

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create two assets ('Filter Asset A' and 'Filter Asset B'), create 'Task For A' on Filter Asset A and 'Task For B' on Filter Asset B, then navigate to /maintenance
    - expect: Both 'Task For A' and 'Task For B' occurrence cards are visible
  2. Select 'Filter Asset A' from the asset filter dropdown
    - expect: Only the 'Task For A' occurrence card is visible
    - expect: The 'Task For B' occurrence card is no longer shown
    - expect: No full page reload occurs (Livewire reactive update)

#### 3.4. Resetting the filter to 'All Assets' shows all occurrences again

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create two assets each with one task ('Task For A', 'Task For B'), navigate to /maintenance, and select 'Filter Asset A' from the filter dropdown
    - expect: Only 'Task For A' occurrence is visible
  2. Select 'All Assets' from the asset filter dropdown
    - expect: Both 'Task For A' and 'Task For B' occurrence cards are visible again

#### 3.5. Overdue occurrence displays a red 'Overdue' badge

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create one asset, navigate to its maintenance page, and create a task named 'Overdue Task' with Start Date 2020-01-01 (a past date)
    - expect: The task card for 'Overdue Task' appears with a past due date
  2. Navigate to /maintenance
    - expect: The 'Overdue Task' occurrence card is visible
    - expect: A badge with the text 'Overdue' is shown on the card

#### 3.6. Future occurrence does not display an 'Overdue' badge

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create one asset, and create a task named 'Future Task' with Start Date 2029-06-01
    - expect: The task card for 'Future Task' appears with a future due date
  2. Navigate to /maintenance
    - expect: The 'Future Task' occurrence card is visible
    - expect: No 'Overdue' badge is shown on the 'Future Task' card

#### 3.7. Empty state message appears when a filtered asset has no pending occurrences

**File:** `e2e/maintenance-view-schedule.spec.ts`

**Steps:**
  1. Register a new user, create two assets ('Asset With Tasks' and 'Empty Asset'), create one task on 'Asset With Tasks' only, then navigate to /maintenance
    - expect: One occurrence card is visible
  2. Select 'Empty Asset' from the asset filter dropdown
    - expect: The text 'No pending maintenance tasks.' is visible
    - expect: The text 'All caught up!' is visible
    - expect: No occurrence cards are shown

### 4. US3 — Mark Occurrence Complete

**Seed:** `e2e/seed.spec.ts`

#### 4.1. Mark Complete on /maintenance removes the occurrence and shows the next auto-generated occurrence

**File:** `e2e/maintenance-mark-complete.spec.ts`

**Steps:**
  1. Register a new user, create one asset, navigate to its maintenance page, and create a monthly task named 'Complete Me Task' with no start date (due today)
    - expect: The 'Complete Me Task' card appears in the per-asset task list
  2. Navigate to /maintenance
    - expect: An occurrence card for 'Complete Me Task' is visible with a 'Mark Complete' button
    - expect: Note the due date shown on the card (it should be today's date)
  3. Click the 'Mark Complete' button on the 'Complete Me Task' occurrence card
    - expect: The occurrence card for 'Complete Me Task' (with today's due date) disappears from the list without a full page reload
    - expect: A new occurrence card for 'Complete Me Task' appears with a due date approximately one month later

#### 4.2. Mark Complete on the per-asset view updates the task card with the next due date

**File:** `e2e/maintenance-mark-complete.spec.ts`

**Steps:**
  1. Register a new user, create one asset, navigate to its maintenance page, and create a monthly task named 'Per Asset Complete Task'
    - expect: The 'Per Asset Complete Task' card is visible with a due date and a 'Mark Complete' button
  2. Note the current due date displayed on the card, then click 'Mark Complete'
    - expect: The task card updates reactively without a full page reload
    - expect: The due date on the card changes to approximately one month after the previously noted date
    - expect: The task remains visible in the per-asset list with the new next due date

#### 4.3. 'Saving…' loading text appears on the Mark Complete button while the request is in-flight

**File:** `e2e/maintenance-mark-complete.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create one task, then navigate to /maintenance so an occurrence card with a 'Mark Complete' button is visible
    - expect: An occurrence card with a 'Mark Complete' button is visible
  2. Click the 'Mark Complete' button and immediately observe the button state before the response returns
    - expect: The button text transitions to 'Saving…' while the Livewire request is in-flight
    - expect: The button is disabled (not interactive) during the in-flight state
    - expect: After the request completes the occurrence card is removed/updated

### 5. US4 — Edit Occurrence Due Date (Inline)

**Seed:** `e2e/seed.spec.ts`

#### 5.1. Pencil icon opens the inline date editor pre-filled with the current due date

**File:** `e2e/maintenance-edit-due-date.spec.ts`

**Steps:**
  1. Register a new user, create one asset, navigate to its maintenance page, and create a task named 'Pencil Edit Task' with Start Date 2027-06-15
    - expect: A task card for 'Pencil Edit Task' shows 'Due: Jun 15, 2027' and a pencil icon button
  2. Click the pencil icon button on the 'Pencil Edit Task' card
    - expect: The 'Due: Jun 15, 2027' text is replaced by a date input
    - expect: The date input is pre-filled with 2027-06-15
    - expect: A 'Save' button is visible next to the date input
    - expect: A 'Cancel' button is visible next to the date input
    - expect: The pencil icon is no longer visible on that card

#### 5.2. Cancel closes the inline editor and restores the original due date without saving changes

**File:** `e2e/maintenance-edit-due-date.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create a task named 'Cancel Edit Task' with Start Date 2027-08-20, then navigate to the asset's maintenance page
    - expect: The task card shows 'Due: Aug 20, 2027' and a pencil icon
  2. Click the pencil icon to open the inline editor, then change the date to 2029-01-01
    - expect: The date input shows 2029-01-01
  3. Click the 'Cancel' button
    - expect: The date input and Save/Cancel buttons disappear without a page reload
    - expect: The task card displays 'Due: Aug 20, 2027' (the original date)
    - expect: The pencil icon button is visible again

#### 5.3. Save persists the new due date and returns the card to view mode

**File:** `e2e/maintenance-edit-due-date.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create a task named 'Date Update Task' with Start Date 2027-05-10, then navigate to the asset's maintenance page
    - expect: The task card shows 'Due: May 10, 2027' and a pencil icon
  2. Click the pencil icon, clear the date input, and type 2028-11-30
  3. Click the 'Save' button
    - expect: The date input and Save/Cancel buttons disappear without a page reload
    - expect: The task card now displays 'Due: Nov 30, 2028'
    - expect: The pencil icon is visible again

#### 5.4. Opening the inline editor on a second card closes the editor on the first card

**File:** `e2e/maintenance-edit-due-date.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create two tasks: 'Edit Task One' (Start Date 2027-04-01) and 'Edit Task Two' (Start Date 2027-07-01), then navigate to the asset's maintenance page
    - expect: Both task cards are visible, each with a pencil icon
  2. Click the pencil icon on the 'Edit Task One' card
    - expect: The inline date editor opens on 'Edit Task One'
    - expect: 'Edit Task Two' still shows its due date text and pencil icon (not in edit mode)
  3. Click the pencil icon on the 'Edit Task Two' card
    - expect: The inline date editor opens on 'Edit Task Two'
    - expect: 'Edit Task One' returns to view mode — the date input is gone and the original due date text is restored

#### 5.5. No pencil icon or inline editor is available on the /maintenance consolidated schedule page

**File:** `e2e/maintenance-edit-due-date.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create one task, then navigate to /maintenance
    - expect: An occurrence card is visible with the task name, asset name, due date, and 'Mark Complete' button
  2. Inspect the occurrence card for pencil icon buttons or date inputs
    - expect: No pencil icon button is present on any occurrence card on the /maintenance page
    - expect: No date input is present on any occurrence card on the /maintenance page

### 6. Task Deactivation

**Seed:** `e2e/seed.spec.ts`

#### 6.1. Clicking Deactivate shows a browser native confirm dialog with the correct message

**File:** `e2e/maintenance-deactivate-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, navigate to its maintenance page, and create a task named 'Dialog Check Task'
    - expect: The task card for 'Dialog Check Task' is visible with a 'Deactivate' button
  2. Click the 'Deactivate' button on 'Dialog Check Task'
    - expect: A browser-native confirm dialog appears
    - expect: The dialog message reads exactly: 'Deactivate this task? Its history will be preserved.'

#### 6.2. Cancelling the confirm dialog leaves the task visible and unchanged in the list

**File:** `e2e/maintenance-deactivate-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create a task named 'Keep Me Task', and navigate to the asset's maintenance page
    - expect: The 'Keep Me Task' card is visible with a 'Deactivate' button
  2. Click 'Deactivate' on 'Keep Me Task', then click Cancel (dismiss) on the browser dialog
    - expect: The dialog closes
    - expect: The 'Keep Me Task' card remains visible in the task list
    - expect: No change to the task list occurs

#### 6.3. Confirming deactivation removes the task card from the per-asset list without a page reload

**File:** `e2e/maintenance-deactivate-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, and create two tasks: 'Remove Me Task' and 'Preserve This Task'
    - expect: Both task cards are visible in the asset's maintenance task list
  2. Click 'Deactivate' on 'Remove Me Task', then click OK (accept) on the browser dialog
    - expect: The 'Remove Me Task' card is removed from the task list without a full page reload
    - expect: The 'Preserve This Task' card remains visible and unaffected

#### 6.4. A deactivated task's occurrence no longer appears on /maintenance

**File:** `e2e/maintenance-deactivate-task.spec.ts`

**Steps:**
  1. Register a new user, create one asset, create a task named 'Schedule Gone Task', then navigate to /maintenance
    - expect: An occurrence card for 'Schedule Gone Task' is visible on the schedule
  2. Navigate back to the asset's maintenance page, click 'Deactivate' on 'Schedule Gone Task', and confirm the dialog
    - expect: The 'Schedule Gone Task' card disappears from the per-asset task list
  3. Navigate to /maintenance
    - expect: No occurrence card for 'Schedule Gone Task' is present on the consolidated Maintenance Schedule

### 7. Navigation and Page Structure

**Seed:** `e2e/seed.spec.ts`

#### 7.1. Authenticated sidebar shows Dashboard, Assets, and Maintenance navigation links

**File:** `e2e/maintenance-navigation.spec.ts`

**Steps:**
  1. Register a new user and observe the sidebar navigation
    - expect: A link labelled 'Dashboard' is visible in the sidebar
    - expect: A link labelled 'Assets' is visible in the sidebar
    - expect: A link labelled 'Maintenance' is visible in the sidebar

#### 7.2. 'View Maintenance' link on the asset detail panel navigates to the per-asset maintenance page

**File:** `e2e/maintenance-navigation.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Nav Test Asset', and navigate to /assets
    - expect: The 'Nav Test Asset' card is visible in the asset list
  2. Click on the 'Nav Test Asset' card to open the detail panel
    - expect: The asset detail panel opens showing a 'View Maintenance' link
  3. Click the 'View Maintenance' link
    - expect: The browser navigates to the per-asset maintenance page for 'Nav Test Asset'
    - expect: The URL matches /assets/{id}/maintenance
    - expect: The page heading 'Nav Test Asset — Maintenance Tasks' is visible

#### 7.3. Per-asset maintenance page heading uses the format '{Asset Name} — Maintenance Tasks'

**File:** `e2e/maintenance-navigation.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Heading Check Unit', and navigate to its maintenance page
    - expect: The page heading text is 'Heading Check Unit — Maintenance Tasks'
    - expect: The 'Add Maintenance Task' form is rendered below the heading
    - expect: The task list section or empty state is rendered below the form

#### 7.4. Clicking Maintenance in the sidebar from the per-asset view navigates to /maintenance

**File:** `e2e/maintenance-navigation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to that asset's per-asset maintenance page
    - expect: The per-asset maintenance page is visible
  2. Click the 'Maintenance' link in the sidebar navigation
    - expect: The browser navigates to /maintenance
    - expect: The page heading 'Maintenance Schedule' is visible
    - expect: The asset filter dropdown is visible on the page
