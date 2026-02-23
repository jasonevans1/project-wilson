import { test, expect } from './maintenance-fixtures';

test.describe('US1 — Create a Maintenance Task', () => {
  test('happy path: create a monthly task with required fields only, form resets and card appears', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test HVAC', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Test HVAC');

    await expect(page.getByText('No maintenance tasks yet.')).toBeVisible();
    await expect(page.getByText('Add Maintenance Task')).toBeVisible();

    await page.getByLabel('Task Name').fill('Replace Air Filter');
    await page.getByRole('button', { name: 'Add Task' }).click();

    // Task card appears
    await expect(page.getByText('Replace Air Filter').first()).toBeVisible({ timeout: 10000 });

    // Empty state gone
    await expect(page.getByText('No maintenance tasks yet.')).not.toBeVisible();

    // Card shows recurrence and has expected buttons
    await expect(page.getByText('Every 1 Monthly')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Mark Complete' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Deactivate' })).toBeVisible();

    // Form resets
    await expect(page.getByLabel('Task Name')).toHaveValue('');
  });

  test('happy path: create a yearly task with all fields filled including description and future start date', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Boiler Unit', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Boiler Unit');

    await page.getByLabel('Task Name').fill('Annual Boiler Inspection');
    await page.getByLabel('Description').fill('Full system check by certified technician');
    await page.getByLabel('Recurrence').selectOption('yearly');
    await page.getByLabel('Every (count)').clear();
    await page.getByLabel('Every (count)').fill('2');
    await page.getByLabel('Start Date').fill('2027-03-15');
    await page.getByRole('button', { name: 'Add Task' }).click();

    await expect(page.getByText('Annual Boiler Inspection').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Every 2 Yearly')).toBeVisible();
    await expect(page.getByText('Due: Mar 15, 2027')).toBeVisible();
  });

  test('recurrence dropdown contains Daily, Weekly, Monthly, and Yearly options with Monthly as default', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');

    const recurrenceSelect = page.getByLabel('Recurrence');
    await expect(recurrenceSelect).toBeVisible();

    // Monthly is selected by default
    await expect(recurrenceSelect).toHaveValue('monthly');

    // All four options are available
    await expect(recurrenceSelect.locator('option[value="daily"]')).toBeAttached();
    await expect(recurrenceSelect.locator('option[value="weekly"]')).toBeAttached();
    await expect(recurrenceSelect.locator('option[value="monthly"]')).toBeAttached();
    await expect(recurrenceSelect.locator('option[value="yearly"]')).toBeAttached();
  });

  test('validation: submitting with an empty Task Name shows an error and does not create a task', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');

    // Do not fill Task Name
    await page.getByRole('button', { name: 'Add Task' }).click();

    // Form stays visible
    await expect(page.getByLabel('Task Name')).toBeVisible();

    // Empty state stays (no task created)
    await expect(page.getByText('No maintenance tasks yet.')).toBeVisible();
  });

  test('validation: submitting with recurrence count of 0 shows an error and does not create a task', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');

    await page.getByLabel('Task Name').fill('Filter Replacement');
    await page.getByLabel('Every (count)').clear();
    await page.getByLabel('Every (count)').fill('0');
    await page.getByRole('button', { name: 'Add Task' }).click();

    // Form stays visible
    await expect(page.getByLabel('Task Name')).toBeVisible();

    // No task card created
    await expect(page.getByText('No maintenance tasks yet.')).toBeVisible();
  });

  test('a newly created task\'s occurrence appears on the consolidated /maintenance schedule', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'My HVAC Unit', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('My HVAC Unit');

    await createMaintenanceTask({ name: 'Schedule Visibility Check' });

    await expect(page.getByText('Schedule Visibility Check').first()).toBeVisible();

    // Navigate to consolidated schedule
    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Schedule Visibility Check')).toBeVisible();
    await expect(page.getByText('My HVAC Unit')).toBeVisible();
  });
});
