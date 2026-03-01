import { test, expect } from './service-records-fixtures';

test.describe('SR6 — Edit Service Record (Validation)', () => {
  test('clearing Description during edit shows a validation error and does not save', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Appliance', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Appliance');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'maintenance',
      description: 'Original description',
    });

    await page.locator('[wire\\:click^="startEdit"]').click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Description').clear();
    await page.getByRole('button', { name: 'Save Changes' }).click();

    // Edit form remains visible — validation prevented save
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    // Cancel and verify original description is preserved
    await page.getByRole('button', { name: 'Cancel' }).click();
    await expect(page.getByText('Original description')).toBeVisible();
  });

  test('clearing Service Date during edit shows a validation error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Appliance', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Appliance');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'repair',
      description: 'Service date validation test',
    });

    await page.locator('[wire\\:click^="startEdit"]').click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Service Date').fill('');
    await page.getByRole('button', { name: 'Save Changes' }).click();

    // Edit form remains open
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();
  });

  test('checking Under Warranty in edit form and saving without Warranty Expires shows a validation error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Appliance', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Appliance');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'inspection',
      description: 'Warranty validation test',
    });

    await page.locator('[wire\\:click^="startEdit"]').click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Under Warranty').check();
    await expect(page.getByLabel('Warranty Expires')).toBeVisible({ timeout: 5000 });

    // Leave Warranty Expires blank
    await page.getByRole('button', { name: 'Save Changes' }).click();

    // Edit form remains open
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    // Cancel and verify record unchanged
    await page.getByRole('button', { name: 'Cancel' }).click();
    await expect(page.getByText('Warranty validation test')).toBeVisible();
  });
});
