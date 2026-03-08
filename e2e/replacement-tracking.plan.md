# Replacement Tracking E2E Test Plan

## Application Overview

End-to-end Playwright tests for the Replacement Tracking feature of the Wilson home asset management application (Laravel 12 + Livewire 4 + Flux UI Free v2). The feature allows authenticated users to configure replacement lifespans per asset, view a tracking dashboard, record replacements, and manage alerts.

## Test Scenarios

### 1. RT1 — Authentication and Access Control

**Seed:** `e2e/seed.spec.ts`

#### 1.1. unauthenticated user visiting /replacement-tracking is redirected to /login

**File:** `e2e/replacement-tracking-auth.spec.ts`

**Steps:**
  1. Without logging in, navigate directly to https://project-wilson.ddev.site/replacement-tracking
    - expect: The browser is redirected to /login
    - expect: The page shows a login form with Email address and Password fields
    - expect: The heading 'Log in to your account' is visible

#### 1.2. authenticated user can reach /replacement-tracking via the sidebar Replacement Tracking link

**File:** `e2e/replacement-tracking-auth.spec.ts`

**Steps:**
  1. Register a new user and navigate to /dashboard
    - expect: The authenticated dashboard is visible
    - expect: The sidebar shows a 'Replacement Tracking' navigation link
  2. Click the 'Replacement Tracking' link in the sidebar
    - expect: The browser navigates to /replacement-tracking
    - expect: The page heading 'Replacement Tracking' is visible

#### 1.3. authenticated user sees the replacement tracking section in the asset detail panel

**File:** `e2e/replacement-tracking-auth.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Test Heater' (category appliance, location Basement), then click on it to open the detail panel
    - expect: The asset detail panel opens
    - expect: A 'Replacement' section heading is visible within the panel
    - expect: The text 'Replacement tracking not yet configured.' is visible
    - expect: A 'Set Up' button is visible in the Replacement section

### 2. RT2 — Dashboard States and Sorting

**Seed:** `e2e/seed.spec.ts`

#### 2.1. dashboard shows empty state message when the user has no assets

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user (no assets created) and navigate to /replacement-tracking
    - expect: The page heading 'Replacement Tracking' is visible
    - expect: The text 'No assets found. Add an asset to start tracking replacements.' is visible
    - expect: No 'Tracked Assets' section heading is shown
    - expect: No 'Not Yet Tracked' section heading is shown

#### 2.2. dashboard shows untracked asset in 'Not Yet Tracked' section when no tracking is configured

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user and create an asset named 'Ceiling Fan' (category other, location Living Room) without configuring replacement tracking, then navigate to /replacement-tracking
    - expect: The 'Not Yet Tracked' section heading is visible
    - expect: A card for 'Ceiling Fan' is visible under 'Not Yet Tracked'
    - expect: The card shows 'Other · Living Room' as the category and location
    - expect: A 'Set Up' button is visible on the 'Ceiling Fan' card
    - expect: No 'Tracked Assets' section is shown

#### 2.3. dashboard shows tracked asset in 'Tracked Assets' section after tracking is configured

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Water Heater' (category appliance, location Basement), configure replacement tracking with lifespan 10 years and install date 2020-01-15, then navigate to /replacement-tracking
    - expect: The 'Tracked Assets' section heading is visible
    - expect: A card for 'Water Heater' is visible under 'Tracked Assets'
    - expect: The card shows 'Appliance · Basement'
    - expect: The card shows 'Expected Replacement' label with the year '2030'
    - expect: The card shows 'Status' label with a value containing 'years remaining'
    - expect: A progress bar is visible on the card
    - expect: An 'Edit' button is visible on the card
    - expect: A 'Record Replacement' button is visible on the card
    - expect: No 'Not Yet Tracked' section is shown

#### 2.4. dashboard shows both tracked and untracked sections when the user has a mix of assets

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user, create 'Water Heater' (appliance, Basement) and configure tracking (10 years, install date 2020-01-15), then create 'HVAC Unit' (hvac, Attic) without configuring tracking, then navigate to /replacement-tracking
    - expect: The 'Tracked Assets' section heading is visible
    - expect: A card for 'Water Heater' appears under 'Tracked Assets'
    - expect: The 'Not Yet Tracked' section heading is visible
    - expect: A card for 'HVAC Unit' appears under 'Not Yet Tracked'

#### 2.5. overdue asset shows a red Overdue badge and red progress bar on the dashboard

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Old Furnace' (category hvac, location Basement), configure tracking with lifespan 5 years and install date 2010-01-01 (well past the expected replacement date), then navigate to /replacement-tracking
    - expect: The 'Tracked Assets' section shows 'Old Furnace'
    - expect: A red 'Overdue' badge is visible on the 'Old Furnace' card
    - expect: The 'Status' field shows a value containing 'years overdue'
    - expect: The progress bar is red (not blue)

#### 2.6. untracked assets are sorted alphabetically on the dashboard

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user and create three assets without tracking: 'Zebra Fan' (other, Hall), 'Apple Pump' (plumbing, Basement), 'Mango Unit' (hvac, Attic), then navigate to /replacement-tracking
    - expect: The 'Not Yet Tracked' section shows all three assets
    - expect: 'Apple Pump' appears before 'Mango Unit' in the list
    - expect: 'Mango Unit' appears before 'Zebra Fan' in the list

#### 2.7. tracked assets are sorted by days remaining ascending (soonest replacement first) on the dashboard

**File:** `e2e/replacement-tracking-dashboard.spec.ts`

**Steps:**
  1. Register a new user, create 'Near Asset' (appliance, Kitchen) with 5 years lifespan and install date 2022-01-01, and 'Far Asset' (appliance, Basement) with 20 years lifespan and install date 2020-01-01, then navigate to /replacement-tracking
    - expect: Both assets appear under 'Tracked Assets'
    - expect: 'Near Asset' (expected replacement ~2027) appears above 'Far Asset' (expected replacement ~2040) in the list

### 3. RT3 — Configure Replacement Tracking via Asset Detail Panel

**Seed:** `e2e/seed.spec.ts`

#### 3.1. happy path: configure tracking on an untracked asset via the asset detail panel

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Dishwasher' (appliance, Kitchen), click on it to open the detail panel
    - expect: The Replacement section shows 'Replacement tracking not yet configured.'
    - expect: A 'Set Up' button is visible
  2. Click the 'Set Up' button
    - expect: The 'Configure Replacement Tracking' form card appears inline
    - expect: The 'Expected Lifespan (Years)' spinbutton is visible and pre-filled with 10
    - expect: The 'Installation Date' date input is visible and empty
    - expect: A 'Save' button is visible
    - expect: A 'Cancel' button is visible
  3. Fill 'Expected Lifespan (Years)' with 12 and 'Installation Date' with 2019-06-01, then click 'Save'
    - expect: The form disappears without a page reload
    - expect: The Replacement section now shows 'Expected replacement: 2031'
    - expect: A status line showing 'years remaining' is visible
    - expect: A progress bar is visible
    - expect: An 'Edit' button is visible
    - expect: A 'Record Replacement' button is visible
    - expect: The text 'Replacement tracking not yet configured.' is no longer shown

#### 3.2. category default lifespan is pre-filled in the setup form based on asset category

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset in the 'hvac' category, open its detail panel, and click 'Set Up'
    - expect: The 'Expected Lifespan (Years)' spinbutton is pre-filled with 15 (the HVAC category default)
  2. Close the form; create another asset in the 'appliance' category, open its detail panel, and click 'Set Up'
    - expect: The 'Expected Lifespan (Years)' spinbutton is pre-filled with 10 (the appliance category default)

#### 3.3. existing tracking values are pre-filled when editing via the Edit button on the asset detail panel

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset, configure tracking with lifespan 8 and install date 2021-03-15, then click the 'Edit' button in the Replacement section
    - expect: The 'Configure Replacement Tracking' form appears
    - expect: The 'Expected Lifespan (Years)' field is pre-filled with 8
    - expect: The 'Installation Date' field is pre-filled with '2021-03-15'
  2. Change 'Expected Lifespan (Years)' to 12 and click 'Save'
    - expect: The form closes
    - expect: The Replacement section updates to reflect the new expected replacement year

#### 3.4. Cancel button closes the setup form without saving changes

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset, open its detail panel, click 'Set Up', fill in some values
    - expect: The 'Configure Replacement Tracking' form is visible with the filled values
  2. Click 'Cancel'
    - expect: The form disappears
    - expect: The Replacement section still shows 'Replacement tracking not yet configured.'
    - expect: The 'Set Up' button reappears

#### 3.5. validation: submitting setup form with empty Installation Date shows a required error

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset, open its detail panel, click 'Set Up', leave 'Installation Date' empty, and click 'Save'
    - expect: The form remains visible
    - expect: No tracking is saved
    - expect: The browser or Livewire prevents submission due to the missing required date field

#### 3.6. validation: submitting setup form with a future Installation Date shows an error

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset, open its detail panel, click 'Set Up', fill 'Expected Lifespan (Years)' with 10, fill 'Installation Date' with a date in the future (e.g. 2035-01-01), and click 'Save'
    - expect: The form remains visible
    - expect: A validation error message appears near the Installation Date field
    - expect: The error message reads 'The install date field must be a date before or equal to today.'
    - expect: The input is marked invalid ([data-invalid] attribute present)
    - expect: No tracking is saved

#### 3.7. validation: submitting setup form with lifespan out of range shows an error

**File:** `e2e/replacement-tracking-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset, open its detail panel, click 'Set Up', set 'Expected Lifespan (Years)' to 0, fill a valid past installation date, and click 'Save'
    - expect: The form remains visible
    - expect: A validation error appears on the Expected Lifespan (Years) field indicating the minimum is 1
  2. Set 'Expected Lifespan (Years)' to 101, keep the valid past date, and click 'Save'
    - expect: The form remains visible
    - expect: A validation error appears indicating the maximum is 100

### 4. RT4 — Configure Replacement Tracking via Dashboard

**Seed:** `e2e/seed.spec.ts`

#### 4.1. happy path: configure tracking on an untracked asset via the dashboard Set Up button

**File:** `e2e/replacement-tracking-dashboard-setup.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Boiler' (hvac, Basement) without tracking, then navigate to /replacement-tracking
    - expect: 'Boiler' appears under 'Not Yet Tracked' with a 'Set Up' button
  2. Click the 'Set Up' button on the 'Boiler' card
    - expect: The 'Configure Replacement Tracking' form appears below the 'Boiler' card (inline, no page navigation)
    - expect: The 'Expected Lifespan (Years)' spinbutton is pre-filled with the HVAC category default (15)
    - expect: 'Save' and 'Cancel' buttons are visible
  3. Fill 'Installation Date' with 2018-05-20 and click 'Save'
    - expect: The form disappears
    - expect: The dashboard refreshes — 'Boiler' moves from 'Not Yet Tracked' to 'Tracked Assets'
    - expect: The 'Boiler' card under 'Tracked Assets' shows an expected replacement year and status

#### 4.2. Cancel button on dashboard untracked Set Up form closes the form without saving

**File:** `e2e/replacement-tracking-dashboard-setup.spec.ts`

**Steps:**
  1. Register a new user, create an untracked asset, navigate to /replacement-tracking, click 'Set Up', then click 'Cancel'
    - expect: The 'Configure Replacement Tracking' form disappears
    - expect: The asset remains in the 'Not Yet Tracked' section
    - expect: The 'Set Up' button reappears on the asset card

#### 4.3. opening Set Up form closes any currently open Record Replacement form on the dashboard

**File:** `e2e/replacement-tracking-dashboard-setup.spec.ts`

**Steps:**
  1. Register a new user, create a tracked asset (install date 2020-01-01, lifespan 10), navigate to /replacement-tracking, click 'Record Replacement' on the tracked card
    - expect: The 'Record Replacement' form is visible below the tracked asset card
  2. Click 'Edit' on the same tracked asset card
    - expect: The 'Record Replacement' form is replaced by the 'Configure Replacement Tracking' form
    - expect: Only one form is visible at a time

#### 4.4. dashboard Edit button on a tracked asset pre-fills the setup form with existing values

**File:** `e2e/replacement-tracking-dashboard-setup.spec.ts`

**Steps:**
  1. Register a new user, create a tracked asset with lifespan 10 and install date 2022-04-01, navigate to /replacement-tracking, click 'Edit' on the tracked asset card
    - expect: The 'Configure Replacement Tracking' form appears
    - expect: The 'Expected Lifespan (Years)' field shows 10
    - expect: The 'Installation Date' field shows '2022-04-01'

### 5. RT5 — Record Asset Replacement via Asset Detail Panel

**Seed:** `e2e/seed.spec.ts`

#### 5.1. happy path: record a replacement with required field only (New Installation Date)

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Refrigerator' (appliance, Kitchen), configure tracking (lifespan 15, install date 2015-01-01), then click the 'Record Replacement' button in the Replacement section
    - expect: The 'Record Replacement' form card appears inline
    - expect: The 'New Installation Date' date input is visible and empty
    - expect: The 'Expected Lifespan (Years)' spinbutton is pre-filled with 15
    - expect: The 'Replacement Cost (optional)' field is visible and empty
    - expect: The 'Notes (optional)' textarea is visible and empty
    - expect: A 'Save' button and a 'Cancel' button are visible
  2. Fill 'New Installation Date' with 2026-02-01 and click 'Save'
    - expect: The form disappears
    - expect: The Replacement section updates to reflect the new expected replacement year (2041)
    - expect: A 'Replacement History' section appears below the status information
    - expect: The history table shows '2026-02-01' as the date

#### 5.2. happy path: record a replacement with all optional fields filled

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, click 'Record Replacement', fill New Installation Date 2026-03-15, Expected Lifespan (Years) 12, Replacement Cost 750.00, Notes 'Replaced old failing unit', then click 'Save'
    - expect: The form disappears
    - expect: The Replacement section reflects the updated lifespan (expected replacement 2038)
    - expect: A 'Replacement History' entry shows '2026-03-15'
    - expect: The history entry shows '$750.00' for cost

#### 5.3. multiple replacements accumulate in the replacement history section

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, record a first replacement with date 2022-01-01 and cost 300.00
    - expect: The Replacement History shows one entry: '2022-01-01' with '$300.00'
  2. Click 'Record Replacement' again and record a second replacement with date 2026-01-10 and cost 450.00
    - expect: The Replacement History shows two entries
    - expect: The entry '2026-01-10' with '$450.00' is visible
    - expect: The entry '2022-01-01' with '$300.00' is still visible

#### 5.4. Cancel button on Record Replacement form closes the form without saving

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, click 'Record Replacement', fill in 'New Installation Date' and 'Replacement Cost', then click 'Cancel'
    - expect: The form disappears
    - expect: No Replacement History section appears
    - expect: The existing tracking info (years remaining, progress bar) is unchanged

#### 5.5. recording a replacement updates the asset install date and recalculates tracking info

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create an asset 'Old Water Heater' (appliance, Basement), configure tracking with lifespan 10 and install date 2010-01-01 so it is overdue, open its detail panel and confirm it shows overdue status
    - expect: The Replacement section shows 'years overdue'
  2. Click 'Record Replacement', fill 'New Installation Date' with 2026-03-01, and click 'Save'
    - expect: The form closes
    - expect: The Replacement section now shows 'years remaining' (no longer overdue)
    - expect: The expected replacement year is approximately 2036 (today + 10 years)
    - expect: The progress bar is blue (not red)

#### 5.6. validation: submitting Record Replacement with empty New Installation Date is blocked

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, click 'Record Replacement', leave 'New Installation Date' empty, and click 'Save'
    - expect: The form remains visible
    - expect: No replacement event is created
    - expect: The browser prevents submission due to the missing required date field

#### 5.7. validation: submitting Record Replacement with a future New Installation Date shows an error

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, click 'Record Replacement', fill 'New Installation Date' with 2035-01-01, and click 'Save'
    - expect: The form remains visible
    - expect: A validation error alert appears near the 'New Installation Date' field
    - expect: The error message reads 'The installed at field must be a date before or equal to today.'
    - expect: The input is marked invalid ([data-invalid] attribute)
    - expect: No replacement event is created

#### 5.8. validation: submitting Record Replacement with a negative cost shows an error

**File:** `e2e/replacement-tracking-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, click 'Record Replacement', fill a valid past date for 'New Installation Date', enter -100 for 'Replacement Cost (optional)', and click 'Save'
    - expect: The form remains visible
    - expect: A validation error appears on the Replacement Cost field indicating the minimum is 0
    - expect: No replacement event is created

### 6. RT6 — Record Asset Replacement via Dashboard

**Seed:** `e2e/seed.spec.ts`

#### 6.1. happy path: record a replacement from the dashboard Record Replacement button

**File:** `e2e/replacement-tracking-dashboard-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset 'Furnace' (hvac, Basement) with lifespan 20 and install date 2010-01-01 (overdue), navigate to /replacement-tracking
    - expect: 'Furnace' appears in 'Tracked Assets' with an 'Overdue' badge
    - expect: A 'Record Replacement' button is visible on the card
  2. Click 'Record Replacement' on the 'Furnace' card
    - expect: The 'Record Replacement' form appears inline below the 'Furnace' card
    - expect: The 'New Installation Date' date input is empty
    - expect: The 'Expected Lifespan (Years)' spinbutton is pre-filled with 20
    - expect: 'Save' and 'Cancel' buttons are visible
  3. Fill 'New Installation Date' with 2026-01-15 and click 'Save'
    - expect: The form disappears
    - expect: The 'Furnace' card updates — the 'Overdue' badge is no longer shown
    - expect: The status changes from 'years overdue' to 'years remaining'
    - expect: The progress bar changes from red to blue

#### 6.2. opening Record Replacement form on dashboard closes the Setup/Edit form if open

**File:** `e2e/replacement-tracking-dashboard-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, navigate to /replacement-tracking, click 'Edit' to open the setup form
    - expect: The 'Configure Replacement Tracking' form is visible
  2. Click 'Record Replacement' on the same card
    - expect: The 'Configure Replacement Tracking' form disappears
    - expect: The 'Record Replacement' form appears
    - expect: Only one inline form is visible at a time

#### 6.3. Cancel on dashboard Record Replacement form closes the form and leaves the card unchanged

**File:** `e2e/replacement-tracking-dashboard-record.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset, navigate to /replacement-tracking, click 'Record Replacement', fill in some values, then click 'Cancel'
    - expect: The 'Record Replacement' form disappears
    - expect: The asset card remains unchanged with its original status
    - expect: The 'Edit' and 'Record Replacement' buttons are visible again

### 7. RT7 — Navigation and Page Structure

**Seed:** `e2e/seed.spec.ts`

#### 7.1. sidebar 'Replacement Tracking' link navigates to /replacement-tracking from any authenticated page

**File:** `e2e/replacement-tracking-navigation.spec.ts`

**Steps:**
  1. Register a new user and navigate to /assets
    - expect: The 'Replacement Tracking' link is visible in the sidebar
  2. Click the 'Replacement Tracking' link
    - expect: The browser navigates to /replacement-tracking
    - expect: The page heading 'Replacement Tracking' is visible

#### 7.2. navigating away from /replacement-tracking and back preserves the tracked/untracked state

**File:** `e2e/replacement-tracking-navigation.spec.ts`

**Steps:**
  1. Register a new user, create and track an asset 'Boiler' (hvac, Basement), navigate to /replacement-tracking and verify 'Boiler' appears under 'Tracked Assets'
    - expect: 'Boiler' is visible under 'Tracked Assets'
  2. Click 'Assets' in the sidebar to leave the dashboard, then click 'Replacement Tracking' to return
    - expect: The /replacement-tracking page reloads
    - expect: 'Boiler' is still visible under 'Tracked Assets' with the same replacement year and status

#### 7.3. all sidebar navigation links remain accessible from the /replacement-tracking page

**File:** `e2e/replacement-tracking-navigation.spec.ts`

**Steps:**
  1. Register a new user and navigate to /replacement-tracking
    - expect: A 'Dashboard' navigation link is visible in the sidebar
    - expect: An 'Assets' navigation link is visible in the sidebar
    - expect: A 'Replacement Tracking' navigation link is visible in the sidebar
  2. Click 'Assets' in the sidebar
    - expect: The browser navigates to /assets
    - expect: The Assets page loads correctly

### 8. RT8 — Data Isolation and Security

**Seed:** `e2e/seed.spec.ts`

#### 8.1. user cannot see another user's assets on the replacement tracking dashboard

**File:** `e2e/replacement-tracking-security.spec.ts`

**Steps:**
  1. Register User A, create an asset 'User A Heater' (appliance, Basement), configure tracking (lifespan 10, install date 2020-01-01), then log out
    - expect: User A is logged out
  2. Register User B (new account), navigate to /replacement-tracking
    - expect: User B sees 'No assets found. Add an asset to start tracking replacements.'
    - expect: 'User A Heater' is not visible to User B

#### 8.2. user cannot configure tracking on another user's asset via direct access

**File:** `e2e/replacement-tracking-security.spec.ts`

**Steps:**
  1. Register User A, create an asset, note its ID, then log out. Register User B.
    - expect: User B is authenticated
  2. As User B, navigate to /assets and verify User A's asset is not accessible
    - expect: User A's asset is not visible in User B's asset list
    - expect: User B cannot access User A's replacement tracking configuration
