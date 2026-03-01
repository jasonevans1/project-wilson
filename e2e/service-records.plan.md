# Service Record Tracking — E2E Test Plan

## Application Overview

This test plan covers end-to-end browser testing for the Service Record Tracking feature of the Wilson home asset management application. The feature is built with Laravel 12, Livewire 4, and Flux UI Free v2. It allows authenticated users to log, view, edit, and delete service records (e.g. appliance repairs, HVAC servicing) against their home assets.

Key architecture facts discovered during exploration:
- Route: GET /assets/{asset}/service-records — rendered by the ServiceRecordList Livewire component
- Navigation path: Assets page → select asset → "View Service Records" link in detail panel
- Service types (enum): Maintenance, Repair, Inspection, Replacement
- Required fields: Service Date, Service Type, Description
- Optional fields: Provider / Contractor, Cost (numeric, min 0, max 2 decimal places), Under Warranty (checkbox), Warranty Expires (date — conditionally required when Under Warranty is checked)
- Records are sorted by service_date descending (newest first, future dates at top)
- A yellow "Future Date" badge appears on any record with a service_date in the future
- The "Add Service Record" button is hidden while the create form is visible
- Inline editing: clicking the pencil icon replaces the record card with an edit form
- Delete requires confirmation via a browser native confirm() dialog ("Delete this service record? This cannot be undone.")
- Authentication guard: unauthenticated users are redirected to /login
- Authorization: users cannot access another user's asset service records (returns 403)

Fixtures follow the same pattern as the existing maintenance fixtures — extending base fixtures from e2e/fixtures.ts, adding goToAssetServiceRecords and createServiceRecord helpers.

## Test Scenarios

### 1. SR0 — Fixtures & Shared Helpers

**Seed:** `e2e/seed.spec.ts`

#### 1.1. service-records fixtures file exports goToAssetServiceRecords and createServiceRecord helpers

**File:** `e2e/service-records-fixtures.ts`

**Steps:**
  1. Create e2e/service-records-fixtures.ts extending base fixtures from e2e/fixtures.ts
    - expect: File exports a test object that includes registerAndGoToAssets, createAsset, selectAsset from the base fixtures
    - expect: File exports goToAssetServiceRecords(assetName) which clicks 'View Service Records' link after selecting the asset and waits for URL matching /assets/\d+/service-records
    - expect: File exports createServiceRecord(data) which fills and submits the New Service Record form and waits for the record card to appear

### 2. SR1 — Authentication & Access Control

**Seed:** `e2e/seed.spec.ts`

#### 2.1. unauthenticated user visiting a service records URL is redirected to /login

**File:** `e2e/service-records-auth.spec.ts`

**Steps:**
  1. Without logging in, navigate directly to /assets/1/service-records
    - expect: Browser is redirected to the login page (/login)
    - expect: The page shows 'Log in to your account' heading
    - expect: The Email address input is visible

#### 2.2. authenticated user can reach a service records page from the asset detail panel

**File:** `e2e/service-records-auth.spec.ts`

**Steps:**
  1. Register a new user and navigate to /assets
    - expect: Assets page loads successfully
  2. Create an asset named 'HVAC Unit' with category 'hvac' and location 'Basement'
    - expect: Asset appears in the asset list
  3. Click on 'HVAC Unit' in the asset list to open the detail panel
    - expect: Detail panel opens showing the asset name, Edit button, View Maintenance link, View Service Records link, and Archive button
  4. Click the 'View Service Records' link
    - expect: Browser navigates to /assets/{id}/service-records
    - expect: Page heading reads 'HVAC Unit — Service Records'
    - expect: 'Add Service Record' button is visible
    - expect: Empty state message 'No service records yet.' is visible

#### 2.3. user cannot access another user's asset service records via direct URL

**File:** `e2e/service-records-auth.spec.ts`

**Steps:**
  1. Register User A, create an asset, note its URL, then log out
    - expect: User A's asset URL is captured (e.g. /assets/42/service-records)
  2. Register a new User B
    - expect: User B is now authenticated
  3. Navigate directly to User A's service records URL
    - expect: The server returns a 403 Forbidden response
    - expect: User B cannot see User A's service records

### 3. SR2 — View & Empty State

**Seed:** `e2e/seed.spec.ts`

#### 3.1. empty state is shown when an asset has no service records

**File:** `e2e/service-records-view.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Refrigerator' with category 'appliance' and location 'Kitchen'
    - expect: Asset is created successfully
  2. Navigate to the asset's service records page via the 'View Service Records' link
    - expect: Page heading reads 'Refrigerator — Service Records'
    - expect: 'No service records yet.' text is visible
    - expect: 'Add the first record above.' text is visible
    - expect: 'Add Service Record' button is visible

#### 3.2. service record card displays type badge, formatted date, description, provider, and cost

**File:** `e2e/service-records-view.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is open
  2. Create a service record: date 2025-03-20, type Repair, description 'Fixed boiler igniter', provider 'XYZ Plumbing', cost 320.50
    - expect: Service record card appears with 'Repair' badge
    - expect: Card shows 'Mar 20, 2025'
    - expect: Card shows 'Fixed boiler igniter'
    - expect: Card shows 'XYZ Plumbing'
    - expect: Card shows '$320.50'
    - expect: No 'Future Date' badge is shown

#### 3.3. future-dated service record shows a yellow Future Date badge

**File:** `e2e/service-records-view.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is open
  2. Create a service record with a date set to 2027-06-01 (future), type Inspection, description 'Scheduled inspection'
    - expect: Service record card appears with 'Inspection' badge
    - expect: Card shows 'Jun 1, 2027'
    - expect: 'Future Date' badge is visible alongside the date
  3. Inspect the badge color or class on the 'Future Date' badge
    - expect: The badge has a yellow/warning color indicating it is a future-dated record

#### 3.4. records are displayed sorted by service_date descending (newest first)

**File:** `e2e/service-records-view.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is open
  2. Create three service records in any order: date 2024-01-01 (Inspection), date 2025-06-15 (Repair), date 2026-01-10 (Maintenance)
    - expect: All three records appear on the page
  3. Observe the order of the three record cards on the page
    - expect: The 2026-01-10 Maintenance record appears first
    - expect: The 2025-06-15 Repair record appears second
    - expect: The 2024-01-01 Inspection record appears last

#### 3.5. record with optional fields omitted does not display provider or cost sections

**File:** `e2e/service-records-view.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is open
  2. Create a service record with only required fields: date 2025-01-01, type Maintenance, description 'Annual checkup' — leave provider and cost blank
    - expect: Service record card appears showing 'Maintenance' badge, 'Jan 1, 2025', and 'Annual checkup'
    - expect: No provider name text is visible in the card
    - expect: No cost text (e.g. '$0.00') is visible in the card
  3. Verify the Under Warranty checkbox was not checked — no warranty information should appear
    - expect: No warranty expiration date is displayed in the card

### 4. SR3 — Create Service Record (Happy Path)

**Seed:** `e2e/seed.spec.ts`

#### 4.1. create a service record with required fields only

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Water Heater', and navigate to its service records page
    - expect: Empty state 'No service records yet.' is shown
    - expect: 'Add Service Record' button is visible
  2. Click 'Add Service Record'
    - expect: 'New Service Record' form card appears
    - expect: 'Add Service Record' button is hidden while the form is open
    - expect: Form contains: Service Date (date input), Service Type (select), Description (textarea), Provider / Contractor (text input), Cost (number input), Under Warranty (checkbox), Save Record button, and Cancel button
  3. Fill in Service Date: 2026-01-15
    - expect: Service Date field shows '2026-01-15'
  4. Select Service Type: Maintenance
    - expect: 'Maintenance' option is selected
  5. Fill in Description: 'Replaced the HVAC filter and cleaned the vents.'
    - expect: Description field contains the entered text
  6. Leave Provider / Contractor and Cost empty; leave Under Warranty unchecked
    - expect: Optional fields remain empty
  7. Click 'Save Record'
    - expect: Form disappears
    - expect: 'Add Service Record' button reappears
    - expect: A new card appears showing the 'Maintenance' badge, 'Jan 15, 2026', and the description
    - expect: Empty state message is gone

#### 4.2. create a service record with all fields filled including warranty

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is open with empty state
  2. Click 'Add Service Record' and fill: Service Date 2026-03-10, Service Type Repair, Description 'Replaced compressor', Provider 'Cool Air Co.', Cost 875.50
    - expect: All fields are filled correctly
  3. Check the 'Under Warranty' checkbox
    - expect: The 'Warranty Expires' date input appears below the checkbox
  4. Fill 'Warranty Expires': 2029-03-10
    - expect: Warranty Expires field shows '2029-03-10'
  5. Click 'Save Record'
    - expect: Form disappears
    - expect: Record card appears showing: 'Repair' badge, 'Mar 10, 2026', 'Replaced compressor', 'Cool Air Co.', '$875.50'

#### 4.3. create records with each of the four service types

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is open
  2. Verify the Service Type dropdown contains exactly: 'Select type...' (default), Maintenance, Repair, Inspection, Replacement
    - expect: All four service type options are present in the dropdown
  3. Create a record with type Maintenance (date 2025-01-01, description 'Maintenance job')
    - expect: Card shows 'Maintenance' badge
  4. Create a record with type Repair (date 2025-02-01, description 'Repair job')
    - expect: Card shows 'Repair' badge
  5. Create a record with type Inspection (date 2025-03-01, description 'Inspection job')
    - expect: Card shows 'Inspection' badge
  6. Create a record with type Replacement (date 2025-04-01, description 'Replacement job')
    - expect: Card shows 'Replacement' badge

#### 4.4. checking Under Warranty reveals the Warranty Expires field; unchecking hides it

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page; click 'Add Service Record'
    - expect: New Service Record form is open
  2. Confirm the 'Warranty Expires' date input is not visible when 'Under Warranty' is unchecked
    - expect: 'Warranty Expires' input is not present in the DOM or is hidden
  3. Check the 'Under Warranty' checkbox
    - expect: 'Warranty Expires' date input appears immediately (Livewire reactive update via wire:model.live)
  4. Uncheck the 'Under Warranty' checkbox
    - expect: 'Warranty Expires' date input disappears again

#### 4.5. cost of zero is accepted and saved correctly

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page; click 'Add Service Record'
    - expect: Form is open
  2. Fill required fields; enter '0' in the Cost field
    - expect: Cost field contains '0'
  3. Click 'Save Record'
    - expect: Record is saved without errors
    - expect: Record card appears showing '$0.00'
  4. Verify no validation error is shown for cost
    - expect: No cost validation error message is displayed

#### 4.6. past service dates are accepted

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page; click 'Add Service Record'
    - expect: Form is open
  2. Enter a service date far in the past: 2015-07-04; select type Inspection; fill description 'Old inspection record'
    - expect: Fields are filled correctly
  3. Click 'Save Record'
    - expect: Record is saved without validation errors
    - expect: Card shows 'Jul 4, 2015'

#### 4.7. form resets to blank state after successful save

**File:** `e2e/service-records-create.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a first service record
    - expect: First record card is visible
  2. Click 'Add Service Record' again to open the form a second time
    - expect: 'New Service Record' form opens
    - expect: Service Date input is empty
    - expect: Service Type shows 'Select type...'
    - expect: Description textarea is empty
    - expect: Provider / Contractor input is empty
    - expect: Cost input is empty
    - expect: Under Warranty checkbox is unchecked
    - expect: 'Warranty Expires' field is not visible

### 5. SR4 — Create Service Record (Validation)

**Seed:** `e2e/seed.spec.ts`

#### 5.1. submitting with no fields filled shows validation errors and does not create a record

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: Form is open
  2. Click 'Save Record' without filling any field
    - expect: Validation errors appear for Service Date, Service Type, and Description
    - expect: Form remains visible
    - expect: No record card is created
    - expect: Empty state message remains visible
  3. Verify exactly which fields show errors
    - expect: Service Date shows a validation error
    - expect: Service Type shows a validation error
    - expect: Description shows a validation error
    - expect: Provider / Contractor does not show an error
    - expect: Cost does not show an error

#### 5.2. submitting without Service Date shows a validation error

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: Form is open
  2. Fill in Service Type: Repair; fill in Description: 'Missing date test'; leave Service Date empty; click 'Save Record'
    - expect: Validation error appears on the Service Date field
    - expect: No record is created
    - expect: Form remains visible

#### 5.3. submitting without Service Type shows a validation error

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: Form is open
  2. Fill in Service Date: 2026-01-01; fill in Description: 'Missing type test'; leave Service Type as 'Select type...'; click 'Save Record'
    - expect: Validation error appears on the Service Type field
    - expect: No record is created
    - expect: Form remains visible

#### 5.4. submitting without Description shows a validation error

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: Form is open
  2. Fill in Service Date: 2026-01-01; select Service Type: Inspection; leave Description empty; click 'Save Record'
    - expect: Validation error appears on the Description field
    - expect: No record is created
    - expect: Form remains visible

#### 5.5. checking Under Warranty and saving without Warranty Expires shows a validation error

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: Form is open
  2. Fill required fields (date, type, description); check Under Warranty; leave Warranty Expires blank; click 'Save Record'
    - expect: Validation error appears on the Warranty Expires field
    - expect: No record is created
    - expect: Form remains visible
  3. Verify the error message references the warranty expiry date requirement
    - expect: Error text is shown near the Warranty Expires field

#### 5.6. entering a negative cost shows a validation error

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: Form is open
  2. Fill required fields; enter '-50' in the Cost field; click 'Save Record'
    - expect: Validation error appears on the Cost field indicating cost must be at least 0
    - expect: No record is created

#### 5.7. cancelling the create form hides the form without saving

**File:** `e2e/service-records-create-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and click 'Add Service Record'
    - expect: 'New Service Record' form is open
  2. Fill Service Date: 2026-06-01, Service Type: Replacement, Description: 'Test cancel behaviour', Provider: 'Acme Corp', Cost: 500
    - expect: Fields contain the entered values
  3. Click 'Cancel'
    - expect: Form disappears
    - expect: 'Add Service Record' button reappears
    - expect: Empty state message 'No service records yet.' remains visible — no record was saved
    - expect: No record card appears

### 6. SR5 — Edit Service Record (Happy Path)

**Seed:** `e2e/seed.spec.ts`

#### 6.1. clicking the pencil icon opens an inline edit form pre-populated with the existing record's values

**File:** `e2e/service-records-edit.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a service record: date 2025-06-01, type Repair, description 'Original description', provider 'Original Provider', cost 100.00
    - expect: Record card is visible
  2. Click the pencil (edit) icon button on the record card
    - expect: The record card is replaced with an 'Edit Service Record' form
    - expect: Service Date field is pre-filled with '2025-06-01'
    - expect: Service Type is pre-selected as 'Repair'
    - expect: Description field shows 'Original description'
    - expect: Provider / Contractor field shows 'Original Provider'
    - expect: Cost field shows '100.00'
    - expect: 'Save Changes' button and 'Cancel' button are visible

#### 6.2. editing description and cost updates the record card

**File:** `e2e/service-records-edit.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, create a record (date 2025-06-01, type Repair, description 'Original description', cost 100.00), then click the edit icon
    - expect: Edit form opens with pre-populated values
  2. Clear the Description field and type 'Updated description'
    - expect: Description field contains 'Updated description'
  3. Clear the Cost field and enter '250.00'
    - expect: Cost field shows '250.00'
  4. Click 'Save Changes'
    - expect: Edit form disappears and the record card reappears
    - expect: Card shows 'Updated description'
    - expect: Card shows '$250.00'
    - expect: Card still shows 'Repair' badge and 'Jun 1, 2025'

#### 6.3. editing service_date to an older date repositions the record in the sorted list

**File:** `e2e/service-records-edit.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create two records: record A at date 2026-01-10 (Maintenance, 'Newer record') and record B at date 2025-01-01 (Inspection, 'Older record')
    - expect: Record A (2026-01-10) appears above record B (2025-01-01)
  2. Click edit on record A (2026-01-10)
    - expect: Edit form opens for record A
  3. Change the Service Date to 2023-06-01
    - expect: Date field shows '2023-06-01'
  4. Click 'Save Changes'
    - expect: List re-renders with record B (2025-01-01) appearing first
    - expect: Record A (now 2023-06-01) appears last

#### 6.4. editing a record to add a provider and cost updates the card to show those fields

**File:** `e2e/service-records-edit.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a record with only required fields (no provider, no cost)
    - expect: Record card shows no provider text and no cost text
  2. Click the edit icon on the record
    - expect: Edit form opens with empty Provider and Cost fields
  3. Fill in Provider: 'New Contractor Inc.' and Cost: 175.00; click 'Save Changes'
    - expect: Card now shows 'New Contractor Inc.'
    - expect: Card now shows '$175.00'

#### 6.5. editing a record to add warranty info — checking Under Warranty and filling Warranty Expires — saves correctly

**File:** `e2e/service-records-edit.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a record without warranty (Under Warranty unchecked)
    - expect: Record card is visible with no warranty information
  2. Click the edit icon; check 'Under Warranty' in the edit form
    - expect: 'Warranty Expires' date field appears in the edit form
  3. Fill 'Warranty Expires' with '2030-01-01'; click 'Save Changes'
    - expect: Edit form closes
    - expect: Record card reappears without errors

#### 6.6. cancelling edit restores the original record card without changes

**File:** `e2e/service-records-edit.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a record (date 2025-06-01, type Repair, description 'Original description', cost 100.00)
    - expect: Record card is visible
  2. Click the edit icon to open the edit form
    - expect: Edit form opens with pre-populated values
  3. Change the Description to 'Cancelled change' and the Cost to '999.99'
    - expect: Fields show the new (unsaved) values
  4. Click 'Cancel'
    - expect: Edit form disappears
    - expect: Original record card reappears with 'Original description' and '$100.00'
    - expect: No changes were persisted

### 7. SR6 — Edit Service Record (Validation)

**Seed:** `e2e/seed.spec.ts`

#### 7.1. clearing Description during edit shows a validation error and does not save

**File:** `e2e/service-records-edit-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a record with description 'Original description'
    - expect: Record card is visible
  2. Click the edit icon; clear the Description field completely; click 'Save Changes'
    - expect: Validation error appears on the Description field
    - expect: Edit form remains visible
    - expect: Record description is unchanged at 'Original description'

#### 7.2. clearing Service Date during edit shows a validation error

**File:** `e2e/service-records-edit-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a record with date 2025-06-01
    - expect: Record card is visible
  2. Click the edit icon; clear the Service Date field; click 'Save Changes'
    - expect: Validation error appears on the Service Date field
    - expect: Edit form remains open
    - expect: Record date is unchanged

#### 7.3. checking Under Warranty in edit form and saving without Warranty Expires shows a validation error

**File:** `e2e/service-records-edit-validation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a record without warranty
    - expect: Record card is visible
  2. Click the edit icon; check Under Warranty; leave Warranty Expires blank; click 'Save Changes'
    - expect: Validation error appears on the Warranty Expires field
    - expect: Record is not updated

### 8. SR7 — Delete Service Record

**Seed:** `e2e/seed.spec.ts`

#### 8.1. clicking Delete shows a native browser confirmation dialog

**File:** `e2e/service-records-delete.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create a service record
    - expect: Record card with a 'Delete' button is visible
  2. Click the 'Delete' button on the record card
    - expect: A native browser confirm() dialog appears with message: 'Delete this service record? This cannot be undone.'

#### 8.2. confirming the delete dialog removes the record and shows the empty state

**File:** `e2e/service-records-delete.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create one service record
    - expect: Record card is visible; empty state is gone
  2. Click 'Delete' on the record card
    - expect: Browser confirmation dialog appears with 'Delete this service record? This cannot be undone.'
  3. Confirm the dialog (click OK / accept)
    - expect: The record card disappears from the page
    - expect: Empty state 'No service records yet.' and 'Add the first record above.' messages reappear
    - expect: No other records are affected

#### 8.3. dismissing the delete dialog leaves the record intact

**File:** `e2e/service-records-delete.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create one service record
    - expect: Record card is visible
  2. Click 'Delete' on the record card
    - expect: Browser confirmation dialog appears
  3. Dismiss the dialog (click Cancel / dismiss)
    - expect: The record card remains visible and unchanged
    - expect: No data was deleted

#### 8.4. deleting one record does not affect other records for the same asset

**File:** `e2e/service-records-delete.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, and create two records: 'Record A to keep' (Inspection, 2025-01-01) and 'Record B to delete' (Repair, 2025-06-01)
    - expect: Both record cards are visible
  2. Click 'Delete' on 'Record B to delete' and confirm the dialog
    - expect: 'Record B to delete' card is removed
    - expect: 'Record A to keep' card remains visible and unchanged

### 9. SR8 — Data Isolation

**Seed:** `e2e/seed.spec.ts`

#### 9.1. user cannot see service records belonging to another user's asset

**File:** `e2e/service-records-security.spec.ts`

**Steps:**
  1. Register User A; create an asset; navigate to its service records page; create a service record with description 'User A private record'
    - expect: User A's record is visible
  2. Log out as User A
    - expect: User A is logged out
  3. Register User B; navigate to /assets
    - expect: User B sees 'No assets yet.' — User A's assets are not visible
  4. As User B, navigate directly to User A's service records URL (e.g. /assets/{userA_asset_id}/service-records)
    - expect: Server returns 403 Forbidden
    - expect: User B cannot view or interact with User A's records

#### 9.2. user cannot delete another user's service record

**File:** `e2e/service-records-security.spec.ts`

**Steps:**
  1. Register User A; create an asset; create a service record; note the service records URL; log out
    - expect: User A's service record exists in the database
  2. Register User B; note that User B has no assets; log out
    - expect: User B is registered
  3. Confirm that a direct HTTP request from User B to deleteRecord on User A's record would be blocked by the 403 guard in the Livewire component
    - expect: The deleteRecord Livewire action aborts with 403 when the record's user_id does not match the authenticated user's id

### 10. SR9 — Navigation & Page Structure

**Seed:** `e2e/seed.spec.ts`

#### 10.1. page heading includes the asset name and 'Service Records' label

**File:** `e2e/service-records-navigation.spec.ts`

**Steps:**
  1. Register a new user, create an asset named 'Samsung Washer', navigate to its service records page
    - expect: Page heading reads 'Samsung Washer — Service Records'

#### 10.2. sidebar navigation links remain accessible from the service records page

**File:** `e2e/service-records-navigation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, and navigate to its service records page
    - expect: Service records page is loaded
  2. Verify the sidebar links are visible
    - expect: 'Dashboard' nav link is visible
    - expect: 'Assets' nav link is visible
    - expect: 'Maintenance' nav link is visible
  3. Click 'Assets' in the sidebar
    - expect: Browser navigates to /assets
    - expect: Asset list page loads with the created asset visible

#### 10.3. navigating away from service records page and back preserves the list

**File:** `e2e/service-records-navigation.spec.ts`

**Steps:**
  1. Register a new user, create an asset, navigate to its service records page, create a service record
    - expect: Record card is visible
  2. Click 'Assets' in the sidebar to leave the service records page
    - expect: Assets page is loaded
  3. Click on the asset to open its detail panel, then click 'View Service Records' again
    - expect: Service records page reloads
    - expect: The previously created record is still displayed
    - expect: Empty state is NOT shown
