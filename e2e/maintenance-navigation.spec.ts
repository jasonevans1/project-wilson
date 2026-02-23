import { test, expect } from './maintenance-fixtures';

test.describe('Navigation and Page Structure', () => {
  test('authenticated sidebar shows Dashboard, Assets, and Maintenance navigation links', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Assets' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Maintenance' })).toBeVisible();
  });

  test('"View Maintenance" link on the asset detail panel navigates to the per-asset maintenance page', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Nav Test Asset', category: 'other', location: 'Garage' });
    await selectAsset('Nav Test Asset');

    await expect(page.getByRole('link', { name: 'View Maintenance' })).toBeVisible();
    await page.getByRole('link', { name: 'View Maintenance' }).click();

    await page.waitForURL(/\/assets\/\d+\/maintenance/);

    await expect(page.getByText('Nav Test Asset')).toBeVisible();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Maintenance Tasks' })).toBeVisible();
  });

  test('per-asset maintenance page heading uses the format \'{Asset Name} — Maintenance Tasks\'', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Heading Check Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Heading Check Unit');

    await expect(page.getByText('Heading Check Unit')).toBeVisible();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Maintenance Tasks' })).toBeVisible();
    await expect(page.getByText('Add Maintenance Task')).toBeVisible();
    await expect(page.getByText('No maintenance tasks yet.')).toBeVisible();
  });

  test('clicking Maintenance in the sidebar from the per-asset view navigates to /maintenance', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');

    await expect(page.locator('[data-flux-heading]', { hasText: 'Maintenance Tasks' })).toBeVisible();

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Maintenance Schedule')).toBeVisible();
    await expect(page.getByRole('combobox')).toBeVisible();
  });
});
