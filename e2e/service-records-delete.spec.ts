import { test, expect } from './service-records-fixtures';

test.describe('SR7 — Delete Service Record', () => {
  test('clicking Delete shows a native browser confirmation dialog with the correct message', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');
    await createServiceRecord({ date: '2025-06-01', type: 'repair', description: 'Dialog test record' });

    await expect(page.getByRole('button', { name: 'Delete' })).toBeVisible();

    let dialogMessage = '';
    page.once('dialog', async dialog => {
      dialogMessage = dialog.message();
      await dialog.dismiss();
    });

    await page.getByRole('button', { name: 'Delete' }).click();
    await page.waitForTimeout(500);

    expect(dialogMessage).toBe('Delete this service record? This cannot be undone.');
  });

  test('confirming the delete dialog removes the record and shows the empty state', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');
    await createServiceRecord({ date: '2025-06-01', type: 'repair', description: 'Record to delete' });

    await expect(page.getByText('Record to delete')).toBeVisible();
    await expect(page.getByText('No service records yet.')).not.toBeVisible();

    page.once('dialog', dialog => dialog.accept());
    await page.getByRole('button', { name: 'Delete' }).click();

    await expect(page.getByText('Record to delete')).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('No service records yet.')).toBeVisible();
    await expect(page.getByText('Add the first record above.')).toBeVisible();
  });

  test('dismissing the delete dialog leaves the record intact', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');
    await createServiceRecord({ date: '2025-06-01', type: 'repair', description: 'Keep this record' });

    await expect(page.getByText('Keep this record')).toBeVisible();

    page.once('dialog', dialog => dialog.dismiss());
    await page.getByRole('button', { name: 'Delete' }).click();

    await page.waitForTimeout(500);
    await expect(page.getByText('Keep this record')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Delete' })).toBeVisible();
  });

  test('deleting one record does not affect other records for the same asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({ date: '2025-01-01', type: 'inspection', description: 'Record A to keep' });
    await createServiceRecord({ date: '2025-06-01', type: 'repair', description: 'Record B to delete' });

    await expect(page.getByText('Record A to keep')).toBeVisible();
    await expect(page.getByText('Record B to delete')).toBeVisible();

    // Record B (2025-06-01) appears first (sorted desc), so its Delete button is first
    page.once('dialog', dialog => dialog.accept());
    const recordBCard = page.locator('[data-flux-card]', { hasText: 'Record B to delete' });
    await recordBCard.getByRole('button', { name: 'Delete' }).click();

    await expect(page.getByText('Record B to delete')).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Record A to keep')).toBeVisible();
  });
});
