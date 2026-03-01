import { test, expect } from './service-records-fixtures';

test.describe('SR9 — Navigation & Page Structure', () => {
  test('page heading includes the asset name and Service Records label', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Samsung Washer', category: 'appliance', location: 'Laundry Room' });
    await goToAssetServiceRecords('Samsung Washer');

    await expect(page.locator('[data-flux-heading]', { hasText: 'Samsung Washer — Service Records' })).toBeVisible();
  });

  test('sidebar navigation links remain accessible from the service records page', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetServiceRecords('Test Unit');

    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Assets' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Maintenance' })).toBeVisible();

    await page.getByRole('link', { name: 'Assets' }).click();
    await page.waitForURL('**/assets');
    await expect(page.getByRole('button', { name: 'Test Unit' })).toBeVisible();
  });

  test('navigating away from service records page and back preserves the list', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({ date: '2025-06-01', type: 'repair', description: 'Persistent record' });
    await expect(page.getByText('Persistent record')).toBeVisible();

    // Navigate away
    await page.getByRole('link', { name: 'Assets' }).click();
    await page.waitForURL('**/assets');

    // Navigate back
    await selectAsset('Test Unit');
    await page.getByRole('link', { name: 'View Service Records' }).click();
    await page.waitForURL(/\/assets\/\d+\/service-records/);

    await expect(page.getByText('Persistent record')).toBeVisible();
    await expect(page.getByText('No service records yet.')).not.toBeVisible();
  });
});
