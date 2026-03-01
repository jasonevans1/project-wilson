import { test, expect } from './service-records-fixtures';

test.describe('SR4 — Create Service Record (Validation)', () => {
  test('submitting with no fields filled shows validation errors and does not create a record', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');

    await page.getByRole('button', { name: 'Add Service Record' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

    await page.getByRole('button', { name: 'Save Record' }).click();

    // Form remains visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

    // No record created — empty state remains
    await expect(page.getByText('No service records yet.')).toBeVisible();
  });

  test('checking Under Warranty and saving without Warranty Expires shows a validation error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');

    await page.getByRole('button', { name: 'Add Service Record' }).click();

    await page.getByLabel('Service Date').fill('2026-01-01');
    await page.getByLabel('Service Type').selectOption('maintenance');
    await page.getByLabel('Description').fill('Test warranty validation');

    await page.getByLabel('Under Warranty').check();
    await expect(page.getByLabel('Warranty Expires')).toBeVisible();

    // Leave Warranty Expires blank and submit
    await page.getByRole('button', { name: 'Save Record' }).click();

    // Form remains visible — validation error prevented save
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();
  });

  test('entering a negative cost shows a validation error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');

    await page.getByRole('button', { name: 'Add Service Record' }).click();

    await page.getByLabel('Service Date').fill('2026-01-01');
    await page.getByLabel('Service Type').selectOption('repair');
    await page.getByLabel('Description').fill('Negative cost test');
    await page.getByLabel('Cost').fill('-50');

    await page.getByRole('button', { name: 'Save Record' }).click();

    // Form remains visible — validation error prevented save
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();
  });

  test('cancelling the create form hides the form without saving', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');

    await page.getByRole('button', { name: 'Add Service Record' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

    await page.getByLabel('Service Date').fill('2026-06-01');
    await page.getByLabel('Service Type').selectOption('replacement');
    await page.getByLabel('Description').fill('Test cancel behaviour');
    await page.getByLabel('Provider / Contractor').fill('Acme Corp');
    await page.getByLabel('Cost').fill('500');

    await page.getByRole('button', { name: 'Cancel' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).not.toBeVisible();
    await expect(page.getByRole('button', { name: 'Add Service Record' })).toBeVisible();
    await expect(page.getByText('No service records yet.')).toBeVisible();
  });
});
