import { test, expect } from './service-records-fixtures';

test.describe('SR3 — Create Service Record (Happy Path)', () => {
  test('create a service record with required fields only', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'appliance', location: 'Basement' });
    await goToAssetServiceRecords('Water Heater');

    await expect(page.getByText('No service records yet.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Add Service Record' })).toBeVisible();

    await page.getByRole('button', { name: 'Add Service Record' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Add Service Record' })).not.toBeVisible();
    await expect(page.getByLabel('Service Date')).toBeVisible();
    await expect(page.getByLabel('Service Type')).toBeVisible();
    await expect(page.getByLabel('Description')).toBeVisible();
    await expect(page.getByLabel('Provider / Contractor')).toBeVisible();
    await expect(page.getByLabel('Cost')).toBeVisible();
    await expect(page.getByLabel('Under Warranty')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save Record' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();

    await page.getByLabel('Service Date').fill('2026-01-15');
    await page.getByLabel('Service Type').selectOption('maintenance');
    await page.getByLabel('Description').fill('Replaced the HVAC filter and cleaned the vents.');

    await page.getByRole('button', { name: 'Save Record' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).not.toBeVisible();
    await expect(page.getByRole('button', { name: 'Add Service Record' })).toBeVisible();
    await expect(page.getByText('Maintenance').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Jan 15, 2026')).toBeVisible();
    await expect(page.getByText('Replaced the HVAC filter and cleaned the vents.')).toBeVisible();
    await expect(page.getByText('No service records yet.')).not.toBeVisible();
  });

  test('create a service record with all fields filled including warranty', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'HVAC Unit', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('HVAC Unit');

    await expect(page.getByText('No service records yet.')).toBeVisible();

    await page.getByRole('button', { name: 'Add Service Record' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

    await page.getByLabel('Service Date').fill('2026-03-10');
    await page.getByLabel('Service Type').selectOption('repair');
    await page.getByLabel('Description').fill('Replaced compressor');
    await page.getByLabel('Provider / Contractor').fill('Cool Air Co.');
    await page.getByLabel('Cost').fill('875.50');

    await page.getByLabel('Under Warranty').check();
    await expect(page.getByLabel('Warranty Expires')).toBeVisible();
    await page.getByLabel('Warranty Expires').fill('2029-03-10');

    await page.getByRole('button', { name: 'Save Record' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).not.toBeVisible();
    await expect(page.getByText('Repair').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Mar 10, 2026')).toBeVisible();
    await expect(page.getByText('Replaced compressor')).toBeVisible();
    await expect(page.getByText('Cool Air Co.')).toBeVisible();
    await expect(page.getByText('$875.50')).toBeVisible();
  });

  test('create records with each of the four service types', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Appliance', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Appliance');

    // Verify dropdown options
    await page.getByRole('button', { name: 'Add Service Record' }).click();
    const serviceTypeSelect = page.getByLabel('Service Type');
    await expect(serviceTypeSelect.locator('option[value=""]')).toBeAttached();
    await expect(serviceTypeSelect.locator('option[value="maintenance"]')).toBeAttached();
    await expect(serviceTypeSelect.locator('option[value="repair"]')).toBeAttached();
    await expect(serviceTypeSelect.locator('option[value="inspection"]')).toBeAttached();
    await expect(serviceTypeSelect.locator('option[value="replacement"]')).toBeAttached();
    await page.getByRole('button', { name: 'Cancel' }).click();
    await expect(page.getByRole('button', { name: 'Add Service Record' })).toBeVisible();

    await createServiceRecord({ date: '2025-01-01', type: 'maintenance', description: 'Maintenance job' });
    await expect(page.getByText('Maintenance').first()).toBeVisible();

    await createServiceRecord({ date: '2025-02-01', type: 'repair', description: 'Repair job' });
    await expect(page.getByText('Repair').first()).toBeVisible();

    await createServiceRecord({ date: '2025-03-01', type: 'inspection', description: 'Inspection job' });
    await expect(page.getByText('Inspection').first()).toBeVisible();

    await createServiceRecord({ date: '2025-04-01', type: 'replacement', description: 'Replacement job' });
    await expect(page.getByText('Replacement').first()).toBeVisible();
  });

  test('checking Under Warranty reveals the Warranty Expires field; unchecking hides it', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Refrigerator', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Refrigerator');
    await page.getByRole('button', { name: 'Add Service Record' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

    await expect(page.getByLabel('Warranty Expires')).not.toBeVisible();

    await page.getByLabel('Under Warranty').check();
    await expect(page.getByLabel('Warranty Expires')).toBeVisible();

    await page.getByLabel('Under Warranty').uncheck();
    await expect(page.getByLabel('Warranty Expires')).not.toBeVisible();
  });

  test('cost of zero is accepted and saved correctly', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Dishwasher', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Dishwasher');
    await page.getByRole('button', { name: 'Add Service Record' }).click();

    await page.getByLabel('Service Date').fill('2026-01-01');
    await page.getByLabel('Service Type').selectOption('maintenance');
    await page.getByLabel('Description').fill('Zero cost maintenance check');
    await page.getByLabel('Cost').fill('0');

    await page.getByRole('button', { name: 'Save Record' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).not.toBeVisible();
    await expect(page.getByText('$0.00')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Zero cost maintenance check')).toBeVisible();
  });

  test('past service dates are accepted', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'appliance', location: 'Utility Room' });
    await goToAssetServiceRecords('Water Heater');
    await page.getByRole('button', { name: 'Add Service Record' }).click();

    await page.getByLabel('Service Date').fill('2015-07-04');
    await page.getByLabel('Service Type').selectOption('inspection');
    await page.getByLabel('Description').fill('Old inspection record');

    await page.getByRole('button', { name: 'Save Record' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).not.toBeVisible();
    await expect(page.getByText('Jul 4, 2015')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Old inspection record')).toBeVisible();
  });

  test('form resets to blank state after successful save', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Boiler', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Boiler');
    await createServiceRecord({
      date: '2026-01-15',
      type: 'maintenance',
      description: 'First service record',
    });

    await expect(page.getByText('First service record')).toBeVisible();

    await page.getByRole('button', { name: 'Add Service Record' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

    await expect(page.getByLabel('Service Date')).toHaveValue('');
    await expect(page.getByLabel('Service Type')).toHaveValue('');
    await expect(page.getByLabel('Description')).toHaveValue('');
    await expect(page.getByLabel('Provider / Contractor')).toHaveValue('');
    await expect(page.getByLabel('Cost')).toHaveValue('');
    await expect(page.getByLabel('Under Warranty')).not.toBeChecked();
    await expect(page.getByLabel('Warranty Expires')).not.toBeVisible();
  });
});
