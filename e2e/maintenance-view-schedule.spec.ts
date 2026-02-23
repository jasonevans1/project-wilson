import { test, expect } from './maintenance-fixtures';

test.describe('US2 — View Maintenance Schedule', () => {
  test('schedule shows task name, asset name, and formatted due date on each occurrence card', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Boiler Room Unit', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Boiler Room Unit');
    await createMaintenanceTask({ name: 'Check Pressure Valve', startDate: '2028-06-15' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Check Pressure Valve')).toBeVisible();
    await expect(page.getByText('Boiler Room Unit')).toBeVisible();
    await expect(page.getByText('Due: Jun 15, 2028')).toBeVisible();
  });

  test('asset filter dropdown lists all user assets plus "All Assets"', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Unit Alpha', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Unit Alpha');
    await createMaintenanceTask({ name: 'Task Alpha' });

    await page.goto('/assets');
    await createAsset({ name: 'Unit Beta', category: 'electrical', location: 'Garage' });
    await goToAssetMaintenance('Unit Beta');
    await createMaintenanceTask({ name: 'Task Beta' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    const filterSelect = page.getByRole('combobox');
    await expect(filterSelect).toBeVisible();

    await expect(filterSelect.locator('option:not([disabled])', { hasText: 'All Assets' })).toBeAttached();
    await expect(filterSelect.locator('option', { hasText: 'Unit Alpha' })).toBeAttached();
    await expect(filterSelect.locator('option', { hasText: 'Unit Beta' })).toBeAttached();
  });

  test('selecting an asset in the filter shows only occurrences for that asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Filter Asset A', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Filter Asset A');
    await createMaintenanceTask({ name: 'Task For A' });

    await page.goto('/assets');
    await createAsset({ name: 'Filter Asset B', category: 'electrical', location: 'Garage' });
    await goToAssetMaintenance('Filter Asset B');
    await createMaintenanceTask({ name: 'Task For B' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    // Both visible before filtering
    await expect(page.getByText('Task For A')).toBeVisible();
    await expect(page.getByText('Task For B')).toBeVisible();

    // Filter to Asset A
    await page.getByRole('combobox').selectOption({ label: 'Filter Asset A' });

    await expect(page.getByText('Task For A')).toBeVisible();
    await expect(page.getByText('Task For B')).not.toBeVisible();
  });

  test('resetting the filter to "All Assets" shows all occurrences again', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Filter Asset A', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Filter Asset A');
    await createMaintenanceTask({ name: 'Task For A' });

    await page.goto('/assets');
    await createAsset({ name: 'Filter Asset B', category: 'electrical', location: 'Garage' });
    await goToAssetMaintenance('Filter Asset B');
    await createMaintenanceTask({ name: 'Task For B' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    // Filter to Asset A
    await page.getByRole('combobox').selectOption({ label: 'Filter Asset A' });
    await expect(page.getByText('Task For B')).not.toBeVisible();

    // Reset filter
    await page.getByRole('combobox').selectOption({ value: '' });

    await expect(page.getByText('Task For A')).toBeVisible();
    await expect(page.getByText('Task For B')).toBeVisible();
  });

  test('overdue occurrence displays a red "Overdue" badge', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Overdue Task', startDate: '2020-01-01' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Overdue Task')).toBeVisible();
    await expect(page.getByText('Overdue', { exact: true })).toBeVisible();
  });

  test('future occurrence does not display an "Overdue" badge', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Future Task', startDate: '2029-06-01' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Future Task')).toBeVisible();
    await expect(page.getByText('Overdue')).not.toBeVisible();
  });

  test('empty state message appears when a filtered asset has no pending occurrences', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Asset With Tasks', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Asset With Tasks');
    await createMaintenanceTask({ name: 'Some Task' });

    await page.goto('/assets');
    await createAsset({ name: 'Empty Asset', category: 'other', location: 'Garage' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    // Filter to asset with no tasks
    await page.getByRole('combobox').selectOption({ label: 'Empty Asset' });

    await expect(page.getByText('No pending maintenance tasks.')).toBeVisible();
    await expect(page.getByText('All caught up!')).toBeVisible();
  });
});
