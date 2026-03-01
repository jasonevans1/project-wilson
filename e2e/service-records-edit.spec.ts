import { test, expect } from './service-records-fixtures';

test.describe('SR5 — Edit Service Record (Happy Path)', () => {
  test('clicking the pencil icon opens an inline edit form pre-populated with existing values', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'repair',
      description: 'Original description',
      provider: 'Original Provider',
      cost: '100',
    });

    // Click the pencil (edit) icon button
    await page.locator('[wire\\:click^="startEdit"]').click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await expect(page.getByLabel('Service Date')).toHaveValue('2025-06-01');
    await expect(page.getByLabel('Service Type')).toHaveValue('repair');
    await expect(page.getByLabel('Description')).toHaveValue('Original description');
    await expect(page.getByLabel('Provider / Contractor')).toHaveValue('Original Provider');
    await expect(page.getByRole('button', { name: 'Save Changes' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();
  });

  test('editing description and cost updates the record card', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'repair',
      description: 'Original description',
      cost: '100',
    });

    await page.locator('[wire\\:click^="startEdit"]').click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Description').clear();
    await page.getByLabel('Description').fill('Updated description');
    await page.getByLabel('Cost').clear();
    await page.getByLabel('Cost').fill('250.00');

    await page.getByRole('button', { name: 'Save Changes' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Updated description')).toBeVisible();
    await expect(page.getByText('$250.00')).toBeVisible();
    await expect(page.getByText('Repair').first()).toBeVisible();
    await expect(page.getByText('Jun 1, 2025')).toBeVisible();
  });

  test('editing service_date to an older date repositions the record in the sorted list', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({ date: '2026-01-10', type: 'maintenance', description: 'Newer record' });
    await createServiceRecord({ date: '2025-01-01', type: 'inspection', description: 'Older record' });

    const cards = page.locator('[data-flux-card]');
    await expect(cards.nth(0)).toContainText('Jan 10, 2026');
    await expect(cards.nth(1)).toContainText('Jan 1, 2025');

    // Click edit on the first card (record A — 2026-01-10)
    await page.locator('[wire\\:click^="startEdit"]').first().click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Service Date').fill('2023-06-01');
    await page.getByRole('button', { name: 'Save Changes' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).not.toBeVisible({ timeout: 10000 });

    // Record B (2025-01-01) should now be first, Record A (2023-06-01) last
    await expect(cards.nth(0)).toContainText('Jan 1, 2025');
    await expect(cards.nth(1)).toContainText('Jun 1, 2023');
  });

  test('editing a record to add a provider and cost updates the card', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'maintenance',
      description: 'Record without optional fields',
    });

    await page.locator('[wire\\:click^="startEdit"]').click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Provider / Contractor').fill('New Contractor Inc.');
    await page.getByLabel('Cost').fill('175.00');

    await page.getByRole('button', { name: 'Save Changes' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('New Contractor Inc.')).toBeVisible();
    await expect(page.getByText('$175.00')).toBeVisible();
  });

  test('cancelling edit restores the original record card without changes', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Test Unit');

    await createServiceRecord({
      date: '2025-06-01',
      type: 'repair',
      description: 'Original description',
      cost: '100',
    });

    await page.locator('[wire\\:click^="startEdit"]').click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).toBeVisible();

    await page.getByLabel('Description').clear();
    await page.getByLabel('Description').fill('Cancelled change');
    await page.getByLabel('Cost').clear();
    await page.getByLabel('Cost').fill('999.99');

    await page.getByRole('button', { name: 'Cancel' }).click();

    await expect(page.locator('[data-flux-heading]', { hasText: 'Edit Service Record' })).not.toBeVisible();
    await expect(page.getByText('Original description')).toBeVisible();
    await expect(page.getByText('$100.00')).toBeVisible();
  });
});
