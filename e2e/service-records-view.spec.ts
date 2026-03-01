import { test, expect } from './service-records-fixtures';

test.describe('SR2 — View & Empty State', () => {
  test('empty state is shown when an asset has no service records', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Refrigerator', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('Refrigerator');

    await expect(page.locator('[data-flux-heading]', { hasText: 'Refrigerator — Service Records' })).toBeVisible();
    await expect(page.getByText('No service records yet.')).toBeVisible();
    await expect(page.getByText('Add the first record above.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Add Service Record' })).toBeVisible();
  });

  test('service record card displays type badge, formatted date, description, provider, and cost', async ({
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
      date: '2025-03-20',
      type: 'repair',
      description: 'Fixed boiler igniter',
      provider: 'XYZ Plumbing',
      cost: '320.50',
    });

    await expect(page.getByText('Repair').first()).toBeVisible();
    await expect(page.getByText('Mar 20, 2025')).toBeVisible();
    await expect(page.getByText('Fixed boiler igniter')).toBeVisible();
    await expect(page.getByText('XYZ Plumbing')).toBeVisible();
    await expect(page.getByText('$320.50')).toBeVisible();
    await expect(page.getByText('Future Date')).not.toBeVisible();
  });

  test('future-dated service record shows a Future Date badge', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'plumbing', location: 'Basement' });
    await goToAssetServiceRecords('Water Heater');

    await createServiceRecord({
      date: '2027-06-01',
      type: 'inspection',
      description: 'Scheduled inspection',
    });

    await expect(page.getByText('Inspection').first()).toBeVisible();
    await expect(page.getByText('Jun 1, 2027')).toBeVisible();
    await expect(page.getByText('Future Date')).toBeVisible();
  });

  test('records are displayed sorted by service_date descending (newest first)', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'HVAC Unit', category: 'hvac', location: 'Attic' });
    await goToAssetServiceRecords('HVAC Unit');

    await createServiceRecord({ date: '2024-01-01', type: 'inspection', description: 'Old inspection' });
    await createServiceRecord({ date: '2025-06-15', type: 'repair', description: 'Mid repair' });
    await createServiceRecord({ date: '2026-01-10', type: 'maintenance', description: 'Recent maintenance' });

    const cards = page.locator('[data-flux-card]');
    await expect(cards.nth(0)).toContainText('Jan 10, 2026');
    await expect(cards.nth(1)).toContainText('Jun 15, 2025');
    await expect(cards.nth(2)).toContainText('Jan 1, 2024');
  });

  test('record with optional fields omitted does not display provider or cost sections', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Furnace', category: 'hvac', location: 'Basement' });
    await goToAssetServiceRecords('Furnace');

    await createServiceRecord({
      date: '2025-01-01',
      type: 'maintenance',
      description: 'Annual checkup',
    });

    await expect(page.getByText('Maintenance').first()).toBeVisible();
    await expect(page.getByText('Jan 1, 2025')).toBeVisible();
    await expect(page.getByText('Annual checkup')).toBeVisible();
    await expect(page.getByText('$')).not.toBeVisible();
  });
});
