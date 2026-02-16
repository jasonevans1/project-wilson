# Home Asset CRUD Test Plan

## Application Overview

This test plan covers comprehensive end-to-end testing for the Home Asset CRUD (Create, Read, Update, Delete/Archive) feature in the Laravel/Livewire application. The feature allows authenticated users to manage their home assets including appliances, HVAC systems, plumbing, electrical, and other household items. The system uses Livewire 4 components with Flux UI Free v2 for reactive interfaces and includes features such as asset creation, viewing, editing, archiving/restoring, pagination, and real-time updates with success notifications.

## Test Scenarios

### 1. Authentication & Access Control

**Seed:** `tests/seed.spec.ts`

#### 1.1. Unauthenticated User Redirect

**File:** `tests/e2e/auth/unauthenticated-access.spec.ts`

**Steps:**
  1. Navigate to the assets page URL (https://project-wilson.ddev.site/assets) without being logged in
    - expect: User is redirected to the login page
    - expect: Assets page is not accessible
  2. Verify the login page is displayed
    - expect: Login form with email and password fields is visible
    - expect: URL shows /login path

#### 1.2. User Registration and First Login

**File:** `tests/e2e/auth/user-registration.spec.ts`

**Steps:**
  1. Navigate to the registration page
    - expect: Registration form is displayed
  2. Fill in name field with 'Test User'
    - expect: Name field contains the entered value
  3. Fill in email field with a unique email (e.g., 'testuser-{timestamp}@example.com')
    - expect: Email field contains the entered value
  4. Fill in password field with 'password123'
    - expect: Password field contains masked characters
  5. Fill in confirm password field with 'password123'
    - expect: Confirm password field contains masked characters
  6. Click 'Create account' button
    - expect: User is successfully registered
    - expect: User is redirected to the dashboard
    - expect: Authentication is successful
  7. Navigate to the Assets page using the navigation link
    - expect: Assets page loads successfully
    - expect: Empty state is displayed with message 'No assets yet.'
    - expect: URL shows /assets path

#### 1.3. Successful Login

**File:** `tests/e2e/auth/successful-login.spec.ts`

**Steps:**
  1. Navigate to the login page
    - expect: Login form is displayed
  2. Fill in email field with valid credentials
    - expect: Email field contains the entered value
  3. Fill in password field with valid credentials
    - expect: Password field contains masked characters
  4. Click 'Log in' button
    - expect: User is successfully authenticated
    - expect: User is redirected to the dashboard
  5. Click 'Assets' link in the navigation menu
    - expect: Assets page loads successfully
    - expect: Page title shows 'Wilson'
    - expect: Heading shows 'Assets'

### 2. Asset Creation - Happy Path

**Seed:** `tests/seed.spec.ts`

#### 2.1. Create Asset with All Fields

**File:** `tests/e2e/create/create-asset-complete.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
    - expect: Empty state is displayed if no assets exist
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
    - expect: All fields are empty/default
    - expect: Required fields are marked (Name, Category, Location)
    - expect: Optional fields are visible (Purchase Date, Install Date, Warranty Expiration Date, Notes)
  3. Fill in Name field with 'Samsung Refrigerator'
    - expect: Name field contains 'Samsung Refrigerator'
  4. Select 'Appliance' from the Category dropdown
    - expect: Category dropdown shows 'Appliance' as selected
  5. Fill in Location field with 'Kitchen'
    - expect: Location field contains 'Kitchen'
  6. Fill in Purchase Date field with '2024-01-15'
    - expect: Purchase Date field contains '2024-01-15'
  7. Fill in Install Date field with '2024-01-20'
    - expect: Install Date field contains '2024-01-20'
  8. Fill in Warranty Expiration Date field with '2027-01-20'
    - expect: Warranty Expiration Date field contains '2027-01-20'
  9. Fill in Notes field with 'Brand new stainless steel French door refrigerator with ice maker'
    - expect: Notes field contains the entered text
  10. Click the 'Save' button
    - expect: Form is submitted successfully
    - expect: Success banner appears with message 'Asset added.'
    - expect: User is returned to the asset list view
    - expect: The new asset appears in the list showing 'Samsung Refrigerator' with subtitle 'Appliance · Kitchen'
    - expect: Success banner auto-dismisses after 3 seconds

#### 2.2. Create Asset with Required Fields Only

**File:** `tests/e2e/create/create-asset-required-only.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
  3. Fill in Name field with 'HVAC Unit'
    - expect: Name field contains 'HVAC Unit'
  4. Select 'HVAC' from the Category dropdown
    - expect: Category dropdown shows 'HVAC' as selected
  5. Fill in Location field with 'Basement'
    - expect: Location field contains 'Basement'
  6. Leave all optional fields empty (Purchase Date, Install Date, Warranty Expiration Date, Notes)
    - expect: Optional fields remain empty
  7. Click the 'Save' button
    - expect: Form is submitted successfully
    - expect: Success banner appears with message 'Asset added.'
    - expect: User is returned to the asset list view
    - expect: The new asset appears in the list showing 'HVAC Unit' with subtitle 'HVAC · Basement'

#### 2.3. Create Multiple Assets with Different Categories

**File:** `tests/e2e/create/create-multiple-assets.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Create an asset with Category 'Plumbing', Name 'Water Heater', Location 'Garage'
    - expect: Asset is created successfully
    - expect: Asset appears in the list
  3. Create an asset with Category 'Electrical', Name 'Circuit Breaker Panel', Location 'Utility Room'
    - expect: Asset is created successfully
    - expect: Asset appears in the list
    - expect: Now 2 assets are visible
  4. Create an asset with Category 'Roofing', Name 'Asphalt Shingles', Location 'Exterior'
    - expect: Asset is created successfully
    - expect: Asset appears in the list
    - expect: Now 3 assets are visible
  5. Create an asset with Category 'Flooring', Name 'Hardwood Floors', Location 'Living Room'
    - expect: Asset is created successfully
    - expect: Asset appears in the list
    - expect: Now 4 assets are visible
  6. Verify all assets are displayed in the list ordered by creation date (latest first)
    - expect: All 4 assets are visible
    - expect: Assets are displayed in reverse chronological order (Hardwood Floors at top)

### 3. Asset Creation - Validation & Error Handling

**Seed:** `tests/seed.spec.ts`

#### 3.1. Validation Error - Missing Name

**File:** `tests/e2e/create/validation-missing-name.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
  3. Leave Name field empty
    - expect: Name field is empty
  4. Select 'Appliance' from the Category dropdown
    - expect: Category dropdown shows 'Appliance' as selected
  5. Fill in Location field with 'Kitchen'
    - expect: Location field contains 'Kitchen'
  6. Click the 'Save' button
    - expect: Form validation prevents submission
    - expect: Browser's built-in validation message appears for the Name field
    - expect: No asset is created in the database
    - expect: User remains on the form

#### 3.2. Validation Error - Missing Category

**File:** `tests/e2e/create/validation-missing-category.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
  3. Fill in Name field with 'Dishwasher'
    - expect: Name field contains 'Dishwasher'
  4. Leave Category dropdown at default 'Select a category' option
    - expect: Category dropdown shows 'Select a category' (disabled option)
  5. Fill in Location field with 'Kitchen'
    - expect: Location field contains 'Kitchen'
  6. Click the 'Save' button
    - expect: Form validation prevents submission
    - expect: Browser's built-in validation message appears for the Category field
    - expect: No asset is created
    - expect: User remains on the form

#### 3.3. Validation Error - Missing Location

**File:** `tests/e2e/create/validation-missing-location.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
  3. Fill in Name field with 'Microwave'
    - expect: Name field contains 'Microwave'
  4. Select 'Appliance' from the Category dropdown
    - expect: Category dropdown shows 'Appliance' as selected
  5. Leave Location field empty
    - expect: Location field is empty
  6. Click the 'Save' button
    - expect: Form validation prevents submission
    - expect: Browser's built-in validation message appears for the Location field
    - expect: No asset is created
    - expect: User remains on the form

#### 3.4. Validation Error - All Required Fields Missing

**File:** `tests/e2e/create/validation-all-missing.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
  3. Leave all required fields empty (Name, Category, Location)
    - expect: All fields are in their default/empty state
  4. Click the 'Save' button
    - expect: Form validation prevents submission
    - expect: Browser's built-in validation message appears for the first required field
    - expect: No asset is created
    - expect: User remains on the form

#### 3.5. Cancel Asset Creation

**File:** `tests/e2e/create/cancel-creation.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click the 'Add Asset' button
    - expect: Asset creation form is displayed
  3. Fill in Name field with 'Test Asset'
    - expect: Name field contains 'Test Asset'
  4. Select 'Other' from the Category dropdown
    - expect: Category dropdown shows 'Other' as selected
  5. Fill in Location field with 'Test Location'
    - expect: Location field contains 'Test Location'
  6. Click the 'Cancel' button
    - expect: Form is closed without saving
    - expect: User is returned to the asset list view
    - expect: No new asset is created
    - expect: Form data is discarded
    - expect: close-panel Livewire event is dispatched

### 4. Asset Viewing & Details

**Seed:** `tests/seed.spec.ts`

#### 4.1. View Asset List - Empty State

**File:** `tests/e2e/view/empty-state.spec.ts`

**Steps:**
  1. Log in as a user with no assets
    - expect: User is authenticated
  2. Navigate to the Assets page
    - expect: Assets page loads
    - expect: Empty state is displayed
    - expect: Message 'No assets yet.' is visible
    - expect: Message 'Click "Add Asset" to add your first home asset.' is visible
    - expect: 'Add Asset' button is visible
    - expect: 'Show Archived' switch is visible

#### 4.2. View Asset List - Active Assets

**File:** `tests/e2e/view/list-active-assets.spec.ts`

**Steps:**
  1. Log in and create 5 different assets with various categories
    - expect: 5 assets are created successfully
  2. Navigate to the Assets page
    - expect: Assets page loads
    - expect: All 5 active assets are displayed in a list
    - expect: Each asset shows its name as the primary text
    - expect: Each asset shows category and location as subtitle in format 'Category · Location'
    - expect: Assets are ordered by creation date (latest first)
    - expect: 'Show Archived' switch is unchecked

#### 4.3. View Asset Detail - All Fields Populated

**File:** `tests/e2e/view/detail-all-fields.spec.ts`

**Steps:**
  1. Log in and create an asset with all fields populated: Name 'Samsung Refrigerator', Category 'Appliance', Location 'Kitchen', Purchase Date '2024-01-15', Install Date '2024-01-20', Warranty Expiration Date '2027-01-20', Notes 'Brand new stainless steel French door refrigerator with ice maker'
    - expect: Asset is created successfully
  2. Click on the asset in the list
    - expect: Detail view is displayed
    - expect: Heading shows 'Samsung Refrigerator'
    - expect: 'Back' button is visible in the header
    - expect: Category field shows 'Appliance'
    - expect: Location field shows 'Kitchen'
    - expect: Purchase Date field shows '2024-01-15'
    - expect: Install Date field shows '2024-01-20'
    - expect: Warranty Expiration field shows '2027-01-20'
    - expect: Notes section shows the complete notes text
    - expect: 'Edit' button is visible
    - expect: 'Archive' button is visible

#### 4.4. View Asset Detail - Required Fields Only

**File:** `tests/e2e/view/detail-required-only.spec.ts`

**Steps:**
  1. Log in and create an asset with only required fields: Name 'Water Heater', Category 'Plumbing', Location 'Basement'
    - expect: Asset is created successfully
  2. Click on the asset in the list
    - expect: Detail view is displayed
    - expect: Heading shows 'Water Heater'
    - expect: Category field shows 'Plumbing'
    - expect: Location field shows 'Basement'
    - expect: Optional fields (Purchase Date, Install Date, Warranty Expiration, Notes) are not displayed
    - expect: 'Edit' and 'Archive' buttons are visible

#### 4.5. Navigate Back from Asset Detail

**File:** `tests/e2e/view/navigate-back.spec.ts`

**Steps:**
  1. Log in and create an asset
    - expect: Asset is created successfully
  2. Click on the asset to view details
    - expect: Detail view is displayed
  3. Click the 'Back' button in the detail view header
    - expect: User is returned to the asset list view
    - expect: The asset list is displayed
    - expect: close-panel Livewire event is dispatched

#### 4.6. View Multiple Assets from Different Categories

**File:** `tests/e2e/view/multiple-categories.spec.ts`

**Steps:**
  1. Log in and create assets from each category: Appliance, HVAC, Plumbing, Electrical, Roofing, Flooring, Exterior, Other
    - expect: 8 assets are created (one from each category)
  2. Navigate to the Assets page
    - expect: All 8 assets are displayed in the list
    - expect: Each asset correctly displays its category label
  3. Click on each asset one by one
    - expect: Each asset's detail view displays the correct category label
    - expect: Category labels are properly formatted: 'Appliance', 'HVAC', 'Plumbing', 'Electrical', 'Roofing', 'Flooring', 'Exterior', 'Other'

### 5. Asset Editing

**Seed:** `tests/seed.spec.ts`

#### 5.1. Edit Asset - Update Single Field

**File:** `tests/e2e/edit/update-single-field.spec.ts`

**Steps:**
  1. Log in and create an asset with Name 'Refrigerator', Category 'Appliance', Location 'Kitchen'
    - expect: Asset is created successfully
  2. Click on the asset to view details
    - expect: Detail view is displayed
  3. Click the 'Edit' button
    - expect: Edit form is displayed
    - expect: All fields are pre-populated with current values
    - expect: Name shows 'Refrigerator'
    - expect: Category shows 'Appliance'
    - expect: Location shows 'Kitchen'
  4. Change Location field from 'Kitchen' to 'Main Kitchen'
    - expect: Location field contains 'Main Kitchen'
  5. Click the 'Save' button
    - expect: Form is submitted successfully
    - expect: User is returned to detail view (not edit mode)
    - expect: asset-updated Livewire event is dispatched
    - expect: Detail view shows updated Location as 'Main Kitchen'
    - expect: All other fields remain unchanged

#### 5.2. Edit Asset - Update Multiple Fields

**File:** `tests/e2e/edit/update-multiple-fields.spec.ts`

**Steps:**
  1. Log in and create an asset with Name 'Old HVAC', Category 'HVAC', Location 'Basement', Purchase Date '2020-05-10'
    - expect: Asset is created successfully
  2. Click on the asset and then click 'Edit'
    - expect: Edit form is displayed with pre-populated values
  3. Update Name to 'New HVAC System'
    - expect: Name field contains 'New HVAC System'
  4. Update Location to 'Attic'
    - expect: Location field contains 'Attic'
  5. Update Purchase Date to '2024-02-01'
    - expect: Purchase Date field contains '2024-02-01'
  6. Add Install Date '2024-02-05'
    - expect: Install Date field contains '2024-02-05'
  7. Add Warranty Expiration Date '2029-02-05'
    - expect: Warranty Expiration Date field contains '2029-02-05'
  8. Add Notes 'High efficiency system installed by certified technician'
    - expect: Notes field contains the text
  9. Click the 'Save' button
    - expect: Form is submitted successfully
    - expect: User is returned to detail view
    - expect: All updated fields are displayed correctly in detail view

#### 5.3. Edit Asset - Change Category

**File:** `tests/e2e/edit/change-category.spec.ts`

**Steps:**
  1. Log in and create an asset with Category 'Other'
    - expect: Asset is created successfully
  2. Click on the asset and then click 'Edit'
    - expect: Edit form is displayed
  3. Change Category from 'Other' to 'Electrical'
    - expect: Category dropdown shows 'Electrical' as selected
  4. Click the 'Save' button
    - expect: Form is submitted successfully
    - expect: Detail view shows Category as 'Electrical'
    - expect: Asset list shows updated category in subtitle

#### 5.4. Edit Asset - Clear Optional Fields

**File:** `tests/e2e/edit/clear-optional-fields.spec.ts`

**Steps:**
  1. Log in and create an asset with all fields populated including Purchase Date, Install Date, Warranty Expiration Date, and Notes
    - expect: Asset is created successfully with all fields
  2. Click on the asset and then click 'Edit'
    - expect: Edit form is displayed with all fields populated
  3. Clear the Purchase Date field
    - expect: Purchase Date field is empty
  4. Clear the Install Date field
    - expect: Install Date field is empty
  5. Clear the Warranty Expiration Date field
    - expect: Warranty Expiration Date field is empty
  6. Clear the Notes field
    - expect: Notes field is empty
  7. Click the 'Save' button
    - expect: Form is submitted successfully
    - expect: Detail view no longer displays Purchase Date, Install Date, Warranty Expiration Date, or Notes sections

#### 5.5. Edit Asset - Validation Error on Update

**File:** `tests/e2e/edit/validation-on-update.spec.ts`

**Steps:**
  1. Log in and create an asset with Name 'Dishwasher'
    - expect: Asset is created successfully
  2. Click on the asset and then click 'Edit'
    - expect: Edit form is displayed
  3. Clear the Name field (make it empty)
    - expect: Name field is empty
  4. Click the 'Save' button
    - expect: Form validation prevents submission
    - expect: Browser's built-in validation message appears
    - expect: Asset is not updated
    - expect: User remains in edit mode
  5. Fill in Name with 'Updated Dishwasher' and save
    - expect: Form submits successfully
    - expect: Asset is updated with new name

#### 5.6. Cancel Asset Edit

**File:** `tests/e2e/edit/cancel-edit.spec.ts`

**Steps:**
  1. Log in and create an asset with Name 'Original Name', Location 'Original Location'
    - expect: Asset is created successfully
  2. Click on the asset and then click 'Edit'
    - expect: Edit form is displayed with original values
  3. Change Name to 'Modified Name'
    - expect: Name field contains 'Modified Name'
  4. Change Location to 'Modified Location'
    - expect: Location field contains 'Modified Location'
  5. Click the 'Cancel' button
    - expect: Edit mode is exited
    - expect: User is returned to detail view
    - expect: Asset retains original values: Name 'Original Name', Location 'Original Location'
    - expect: Changes are discarded
    - expect: close-panel Livewire event is dispatched

### 6. Asset Archiving

**Seed:** `tests/seed.spec.ts`

#### 6.1. Archive Active Asset

**File:** `tests/e2e/archive/archive-asset.spec.ts`

**Steps:**
  1. Log in and create an active asset
    - expect: Asset is created and appears in active list
  2. Click on the asset to view details
    - expect: Detail view is displayed
    - expect: 'Archive' button is visible
  3. Click the 'Archive' button
    - expect: Archive confirmation modal is displayed
    - expect: Modal heading shows 'Archive this asset?'
    - expect: Modal message explains 'This asset will be moved to the archived list and will no longer appear in your active assets.'
    - expect: 'Cancel' and 'Archive' buttons are visible in modal
  4. Click the 'Archive' button in the modal
    - expect: Modal closes
    - expect: Asset status is changed to 'Archived' in database
    - expect: asset-archived Livewire event is dispatched
    - expect: User is returned to asset list view
    - expect: Asset no longer appears in the active assets list
    - expect: Active list shows empty state or remaining active assets
  5. Toggle 'Show Archived' switch to on
    - expect: Archived assets list is displayed
    - expect: The archived asset appears with an 'Archived' badge
    - expect: Asset shows in format: Name, Badge, 'Category · Location'

#### 6.2. Cancel Archive Confirmation

**File:** `tests/e2e/archive/cancel-archive.spec.ts`

**Steps:**
  1. Log in and create an active asset
    - expect: Asset is created successfully
  2. Click on the asset to view details
    - expect: Detail view is displayed
  3. Click the 'Archive' button
    - expect: Archive confirmation modal is displayed
  4. Click the 'Cancel' button in the modal
    - expect: Modal closes
    - expect: User remains in detail view
    - expect: Asset status remains 'Active'
    - expect: Asset still appears in active list
    - expect: No changes are made to the asset

#### 6.3. Close Archive Modal via X Button

**File:** `tests/e2e/archive/close-modal-x.spec.ts`

**Steps:**
  1. Log in and create an active asset
    - expect: Asset is created successfully
  2. Click on the asset to view details
    - expect: Detail view is displayed
  3. Click the 'Archive' button
    - expect: Archive confirmation modal is displayed
    - expect: 'Close modal' button (X) is visible in top right
  4. Click the 'Close modal' button (X icon)
    - expect: Modal closes
    - expect: User remains in detail view
    - expect: Asset status remains 'Active'
    - expect: No changes are made

#### 6.4. Archive Multiple Assets

**File:** `tests/e2e/archive/archive-multiple.spec.ts`

**Steps:**
  1. Log in and create 5 active assets
    - expect: 5 assets are created and visible in active list
  2. Archive the first asset
    - expect: Asset is archived
    - expect: 4 assets remain in active list
  3. Archive the second asset
    - expect: Asset is archived
    - expect: 3 assets remain in active list
  4. Archive the third asset
    - expect: Asset is archived
    - expect: 2 assets remain in active list
  5. Toggle 'Show Archived' switch to on
    - expect: Archived list shows 3 archived assets
    - expect: Each has an 'Archived' badge
    - expect: Assets are ordered by most recently modified
  6. Toggle 'Show Archived' switch to off
    - expect: Active list shows 2 remaining active assets

### 7. Asset Restoration

**Seed:** `tests/seed.spec.ts`

#### 7.1. Restore Archived Asset

**File:** `tests/e2e/restore/restore-asset.spec.ts`

**Steps:**
  1. Log in and create an asset, then archive it
    - expect: Asset is archived successfully
  2. Toggle 'Show Archived' switch to on
    - expect: Archived asset is visible with 'Archived' badge
  3. Click on the archived asset
    - expect: Detail view is displayed
    - expect: 'Restore' button is visible instead of 'Archive' button
  4. Click the 'Restore' button
    - expect: Asset status is changed to 'Active' in database
    - expect: asset-restored Livewire event is dispatched
    - expect: User remains in archived list view
    - expect: Asset no longer appears in archived list
    - expect: Archived list may show empty state if no other archived assets exist
  5. Toggle 'Show Archived' switch to off
    - expect: Active assets list is displayed
    - expect: The restored asset appears in the active list without any badge

#### 7.2. Restore Multiple Archived Assets

**File:** `tests/e2e/restore/restore-multiple.spec.ts`

**Steps:**
  1. Log in and create 4 assets, then archive all of them
    - expect: 4 assets are archived
  2. Toggle 'Show Archived' switch to on
    - expect: 4 archived assets are visible
  3. Click on the first archived asset and restore it
    - expect: Asset is restored
    - expect: 3 archived assets remain
  4. Click on the second archived asset and restore it
    - expect: Asset is restored
    - expect: 2 archived assets remain
  5. Toggle 'Show Archived' switch to off
    - expect: Active list shows 2 restored assets
    - expect: Assets are ordered by modification date
  6. Toggle 'Show Archived' switch to on
    - expect: Archived list shows 2 remaining archived assets

#### 7.3. Edit Archived Asset and Restore

**File:** `tests/e2e/restore/edit-then-restore.spec.ts`

**Steps:**
  1. Log in and create an asset with Name 'Old Name', then archive it
    - expect: Asset is archived
  2. Toggle 'Show Archived' switch to on and click on the archived asset
    - expect: Detail view is displayed
  3. Click 'Edit' button
    - expect: Edit form is displayed with current values
  4. Update Name to 'Updated Name'
    - expect: Name field contains 'Updated Name'
  5. Click 'Save' button
    - expect: Asset is updated
    - expect: User returns to detail view
    - expect: Name shows 'Updated Name'
    - expect: Asset status remains 'Archived'
  6. Click 'Restore' button
    - expect: Asset is restored to Active status
    - expect: Asset disappears from archived list
  7. Toggle 'Show Archived' switch to off
    - expect: Restored asset appears in active list with updated name 'Updated Name'

### 8. Pagination

**Seed:** `tests/seed.spec.ts`

#### 8.1. Pagination - Exactly 15 Assets

**File:** `tests/e2e/pagination/fifteen-assets.spec.ts`

**Steps:**
  1. Log in and create exactly 15 active assets
    - expect: 15 assets are created
  2. Navigate to the Assets page
    - expect: All 15 assets are displayed on the first page
    - expect: No pagination controls are visible
    - expect: All assets fit on one page

#### 8.2. Pagination - More Than 15 Assets

**File:** `tests/e2e/pagination/multiple-pages.spec.ts`

**Steps:**
  1. Log in and create 23 active assets
    - expect: 23 assets are created
  2. Navigate to the Assets page
    - expect: First 15 assets are displayed on page 1
    - expect: Pagination controls are visible at the bottom
    - expect: Page indicator shows '1' as current page
    - expect: 'Next' or page '2' link is available
  3. Click 'Next' or page '2' link
    - expect: Page 2 loads
    - expect: Remaining 8 assets are displayed
    - expect: Page indicator shows '2' as current page
    - expect: 'Previous' or page '1' link is available
  4. Click 'Previous' or page '1' link
    - expect: Page 1 loads
    - expect: First 15 assets are displayed again

#### 8.3. Pagination - Navigate Between Pages

**File:** `tests/e2e/pagination/navigate-pages.spec.ts`

**Steps:**
  1. Log in and create 40 active assets
    - expect: 40 assets are created
  2. Navigate to the Assets page
    - expect: Page 1 displays 15 assets
    - expect: Pagination shows pages 1, 2, 3
  3. Click on page 2
    - expect: Page 2 displays next 15 assets (16-30)
    - expect: Page 2 is highlighted as current
  4. Click on page 3
    - expect: Page 3 displays remaining 10 assets (31-40)
    - expect: Page 3 is highlighted as current
  5. Click on page 1
    - expect: Page 1 displays first 15 assets again

#### 8.4. Pagination - Archived Assets

**File:** `tests/e2e/pagination/archived-pagination.spec.ts`

**Steps:**
  1. Log in and create 20 assets, then archive all of them
    - expect: 20 assets are archived
    - expect: Active list is empty
  2. Toggle 'Show Archived' switch to on
    - expect: First 15 archived assets are displayed on page 1
    - expect: Pagination controls are visible
    - expect: Page 2 link is available
  3. Click on page 2
    - expect: Remaining 5 archived assets are displayed on page 2
    - expect: All displayed assets have 'Archived' badge

#### 8.5. Pagination - Asset Count After Archive

**File:** `tests/e2e/pagination/count-after-archive.spec.ts`

**Steps:**
  1. Log in and create 16 active assets
    - expect: 16 assets are created
    - expect: Page 1 shows 15 assets
    - expect: Page 2 shows 1 asset
  2. Navigate to page 2
    - expect: 1 asset is displayed on page 2
  3. Click on the asset and archive it
    - expect: Asset is archived
    - expect: User is returned to active list
    - expect: Active list shows page 1 with 15 assets
    - expect: Pagination no longer shows page 2 (only 15 active assets remain)

### 9. Toggle Between Active and Archived

**Seed:** `tests/seed.spec.ts`

#### 9.1. Toggle Show Archived Switch

**File:** `tests/e2e/toggle/basic-toggle.spec.ts`

**Steps:**
  1. Log in and create 3 active assets and archive 2 of them
    - expect: 1 active asset and 2 archived assets exist
  2. Navigate to the Assets page
    - expect: 'Show Archived' switch is unchecked
    - expect: 1 active asset is displayed
  3. Toggle 'Show Archived' switch to on
    - expect: Switch is checked
    - expect: List refreshes to show archived assets
    - expect: 2 archived assets are displayed
    - expect: Each asset has an 'Archived' badge
    - expect: Active asset is not visible
  4. Toggle 'Show Archived' switch to off
    - expect: Switch is unchecked
    - expect: List refreshes to show active assets
    - expect: 1 active asset is displayed
    - expect: Archived assets are not visible

#### 9.2. Toggle Clears Selected Asset

**File:** `tests/e2e/toggle/toggle-clears-selection.spec.ts`

**Steps:**
  1. Log in and create an active asset
    - expect: Active asset is created
  2. Click on the asset to view details
    - expect: Detail view is displayed
  3. Toggle 'Show Archived' switch to on
    - expect: Detail view is closed
    - expect: Archived list is displayed (empty or with archived assets)
    - expect: No asset detail is selected
  4. Toggle 'Show Archived' switch to off
    - expect: Active list is displayed
    - expect: No asset detail is selected

#### 9.3. Toggle with Create Form Open

**File:** `tests/e2e/toggle/toggle-closes-form.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Click 'Add Asset' button
    - expect: Create form is displayed
  3. Toggle 'Show Archived' switch to on
    - expect: Create form is closed
    - expect: Archived assets list is displayed
  4. Toggle 'Show Archived' switch to off
    - expect: Active assets list is displayed
    - expect: Create form remains closed

#### 9.4. Repeated Toggle Actions

**File:** `tests/e2e/toggle/repeated-toggle.spec.ts`

**Steps:**
  1. Log in and create 2 active assets and 2 archived assets
    - expect: 2 active and 2 archived assets exist
  2. Navigate to the Assets page
    - expect: 2 active assets are displayed
  3. Toggle 'Show Archived' on
    - expect: 2 archived assets are displayed
  4. Toggle 'Show Archived' off
    - expect: 2 active assets are displayed
  5. Toggle 'Show Archived' on
    - expect: 2 archived assets are displayed
  6. Toggle 'Show Archived' off
    - expect: 2 active assets are displayed
  7. Verify each toggle transition is smooth and data loads correctly
    - expect: All transitions work correctly
    - expect: Correct assets are displayed for each state
    - expect: No UI glitches or errors occur

### 10. Data Isolation & Security

**Seed:** `tests/seed.spec.ts`

#### 10.1. User Cannot See Other User's Assets

**File:** `tests/e2e/security/user-isolation.spec.ts`

**Steps:**
  1. Create two users: User A and User B
    - expect: Both users are created
  2. Log in as User A and create 3 assets
    - expect: 3 assets are created for User A
  3. Log out and log in as User B
    - expect: User B is authenticated
  4. Navigate to the Assets page
    - expect: Empty state is displayed
    - expect: No assets are visible
    - expect: User B cannot see User A's assets
  5. Create 2 assets as User B
    - expect: 2 assets are created and visible for User B
  6. Log out and log in as User A
    - expect: User A is authenticated
  7. Navigate to the Assets page
    - expect: Only User A's 3 original assets are visible
    - expect: User A cannot see User B's assets

#### 10.2. User Cannot Edit Other User's Assets via Direct Component Access

**File:** `tests/e2e/security/edit-isolation.spec.ts`

**Steps:**
  1. Create two users: User A and User B
    - expect: Both users are created
  2. Log in as User A and create an asset with ID captured
    - expect: Asset is created for User A
  3. Log out and log in as User B
    - expect: User B is authenticated
  4. Attempt to access User A's asset by trying to interact with AssetForm component with User A's asset ID (this tests the backend authorization)
    - expect: Access is denied
    - expect: ModelNotFoundException or authorization error occurs
    - expect: User B cannot modify User A's asset

#### 10.3. User Cannot Archive Other User's Assets

**File:** `tests/e2e/security/archive-isolation.spec.ts`

**Steps:**
  1. Create two users: User A and User B
    - expect: Both users are created
  2. Log in as User A and create an asset
    - expect: Asset is created for User A
  3. Log out and log in as User B
    - expect: User B is authenticated
  4. Attempt to archive User A's asset by trying to interact with AssetDetail component with User A's asset ID
    - expect: Access is denied
    - expect: ModelNotFoundException or authorization error occurs
    - expect: User B cannot archive User A's asset

### 11. UI Responsiveness & Real-time Updates

**Seed:** `tests/seed.spec.ts`

#### 11.1. Success Banner Auto-Dismiss

**File:** `tests/e2e/ui/success-banner-dismiss.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page
    - expect: Assets page is loaded
  2. Create a new asset
    - expect: Asset is created successfully
    - expect: Success banner appears with message 'Asset added.'
  3. Wait for 3 seconds
    - expect: Success banner automatically fades out and disappears
    - expect: Banner is no longer visible after 3 seconds

#### 11.2. List Updates After Create

**File:** `tests/e2e/ui/list-update-after-create.spec.ts`

**Steps:**
  1. Log in and navigate to the Assets page with empty state
    - expect: Empty state is displayed
  2. Create an asset
    - expect: Asset is created
    - expect: Empty state disappears
    - expect: Asset list is displayed with the new asset
    - expect: Asset appears at the top of the list
  3. Create another asset
    - expect: New asset appears at the top of the list
    - expect: Previous asset moves down
    - expect: List shows 2 assets total

#### 11.3. List Updates After Archive

**File:** `tests/e2e/ui/list-update-after-archive.spec.ts`

**Steps:**
  1. Log in and create 3 active assets
    - expect: 3 assets are displayed in active list
  2. Click on the first asset and archive it
    - expect: Asset is archived
    - expect: Active list automatically updates
    - expect: Only 2 assets remain in active list
    - expect: Archived asset is no longer visible
  3. Toggle 'Show Archived' to on
    - expect: Archived list displays the 1 archived asset

#### 11.4. List Updates After Restore

**File:** `tests/e2e/ui/list-update-after-restore.spec.ts`

**Steps:**
  1. Log in and create 2 assets, then archive both
    - expect: 2 archived assets exist
    - expect: Active list is empty
  2. Toggle 'Show Archived' to on
    - expect: 2 archived assets are displayed
  3. Click on the first archived asset and restore it
    - expect: Asset is restored
    - expect: Archived list automatically updates
    - expect: Only 1 asset remains in archived list
  4. Toggle 'Show Archived' to off
    - expect: Active list displays the 1 restored asset

#### 11.5. Livewire Events Dispatched Correctly

**File:** `tests/e2e/ui/livewire-events.spec.ts`

**Steps:**
  1. Log in and create an asset
    - expect: 'asset-created' Livewire event is dispatched
    - expect: AssetList component receives the event
    - expect: Success banner is shown
  2. Edit an asset
    - expect: 'asset-updated' Livewire event is dispatched
    - expect: AssetDetail component receives the event
    - expect: Component refreshes with updated data
  3. Archive an asset
    - expect: 'asset-archived' Livewire event is dispatched
    - expect: AssetList component receives the event
    - expect: Selected asset is cleared
  4. Restore an asset
    - expect: 'asset-restored' Livewire event is dispatched
    - expect: AssetList component receives the event
    - expect: Selected asset is cleared
  5. Click Cancel on create form
    - expect: 'close-panel' Livewire event is dispatched
    - expect: AssetList component receives the event
    - expect: Form is closed

### 12. Edge Cases & Boundary Conditions

**Seed:** `tests/seed.spec.ts`

#### 12.1. Very Long Asset Name

**File:** `tests/e2e/edge-cases/long-name.spec.ts`

**Steps:**
  1. Log in and click 'Add Asset'
    - expect: Create form is displayed
  2. Enter a name with 255 characters (the maximum allowed)
    - expect: Name field accepts all 255 characters
  3. Fill in Category and Location, then save
    - expect: Asset is created successfully
    - expect: Full name is stored and displayed correctly in list and detail view
  4. Attempt to enter a name with 256 characters
    - expect: Validation error occurs or field prevents entry beyond 255 characters

#### 12.2. Very Long Notes Text

**File:** `tests/e2e/edge-cases/long-notes.spec.ts`

**Steps:**
  1. Log in and create an asset
    - expect: Asset is created
  2. Edit the asset and add Notes with 2000 characters (the maximum)
    - expect: Notes field accepts all 2000 characters
  3. Save the asset
    - expect: Asset is saved successfully
    - expect: Full notes text is displayed in detail view
  4. Edit the asset again and attempt to enter 2001 characters in Notes
    - expect: Validation error occurs or field prevents entry beyond 2000 characters

#### 12.3. Special Characters in Asset Fields

**File:** `tests/e2e/edge-cases/special-characters.spec.ts`

**Steps:**
  1. Log in and create an asset with Name containing special characters: 'Fridge & Freezer <Model #123>'
    - expect: Asset is created
  2. Create another asset with Location containing special characters: 'Kitchen (Main) / Pantry Area'
    - expect: Asset is created
  3. Create another asset with Notes containing quotes, apostrophes, and other special characters
    - expect: Asset is created
  4. View each asset's detail
    - expect: All special characters are properly escaped and displayed
    - expect: No XSS vulnerabilities
    - expect: No encoding issues

#### 12.4. Date Field Edge Cases

**File:** `tests/e2e/edge-cases/date-fields.spec.ts`

**Steps:**
  1. Log in and create an asset with future Purchase Date
    - expect: Asset is created (no date validation preventing future dates)
    - expect: Future date is accepted and displayed
  2. Create an asset with Install Date before Purchase Date
    - expect: Asset is created (no cross-field date validation)
    - expect: Both dates are accepted
  3. Create an asset with Warranty Expiration Date in the past
    - expect: Asset is created
    - expect: Past date is accepted
  4. Create an asset with very old dates (e.g., 1950-01-01)
    - expect: Asset is created
    - expect: Old dates are handled correctly

#### 12.5. Rapid Action Sequences

**File:** `tests/e2e/edge-cases/rapid-actions.spec.ts`

**Steps:**
  1. Log in and create an asset
    - expect: Asset is created
  2. Quickly click on the asset, click Edit, modify a field, click Save, all in rapid succession
    - expect: All actions are handled correctly
    - expect: No race conditions
    - expect: Asset is updated properly
  3. Rapidly toggle 'Show Archived' switch on and off multiple times
    - expect: List updates correctly each time
    - expect: No errors or state corruption
    - expect: Final state matches switch position
  4. Create an asset, immediately click on it, immediately click Archive, immediately confirm archive
    - expect: All actions complete successfully
    - expect: Asset is archived
    - expect: No errors occur

#### 12.6. Browser Back Button Navigation

**File:** `tests/e2e/edge-cases/browser-back-button.spec.ts`

**Steps:**
  1. Log in and navigate to Assets page
    - expect: Assets page is loaded
  2. Create an asset
    - expect: Asset is created
    - expect: Asset list is displayed
  3. Click browser's back button
    - expect: Application handles back navigation gracefully
    - expect: User experience remains consistent
  4. Click on an asset to view details
    - expect: Detail view is displayed
  5. Click browser's back button
    - expect: User returns to asset list
    - expect: State is preserved correctly

#### 12.7. Empty String vs Null for Optional Fields

**File:** `tests/e2e/edge-cases/empty-vs-null.spec.ts`

**Steps:**
  1. Log in and create an asset with Notes containing whitespace only (spaces, tabs)
    - expect: Asset is created
  2. View the asset detail
    - expect: Notes field handling is correct (trimmed or stored as-is based on backend logic)
  3. Edit the asset and clear Notes completely
    - expect: Asset updates successfully
    - expect: Notes field is no longer displayed in detail view

### 13. Performance & Load Testing

**Seed:** `tests/seed.spec.ts`

#### 13.1. Load Time with Many Assets

**File:** `tests/e2e/performance/many-assets-load.spec.ts`

**Steps:**
  1. Log in and create 100 active assets
    - expect: 100 assets are created
  2. Navigate to the Assets page
    - expect: Page loads within acceptable time (e.g., under 3 seconds)
    - expect: First page of 15 assets is displayed
    - expect: Pagination works correctly
  3. Navigate through all pages
    - expect: Each page loads quickly
    - expect: No performance degradation
    - expect: All pages display correct assets

#### 13.2. Search Through Large Dataset

**File:** `tests/e2e/performance/large-dataset-navigation.spec.ts`

**Steps:**
  1. Log in and create 50 active assets and 50 archived assets
    - expect: 100 total assets are created
  2. Navigate through active assets pages
    - expect: Pagination handles 50 active assets across 4 pages
    - expect: Performance remains good
  3. Toggle to archived assets
    - expect: Pagination handles 50 archived assets across 4 pages
    - expect: Toggle switch response is fast
  4. Click through multiple assets to view details
    - expect: Detail views load quickly
    - expect: Navigation is smooth
