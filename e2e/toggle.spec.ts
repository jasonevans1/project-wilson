import { test, expect } from './fixtures';

test.describe('Toggle Between Active and Archived', () => {
  test('basic toggle between active and archived views', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    // Create 3 assets, archive 2
    await createAsset({ name: 'Active One', category: 'other', location: 'A' });
    await createAsset({ name: 'To Archive 1', category: 'other', location: 'B' });
    await createAsset({ name: 'To Archive 2', category: 'other', location: 'C' });

    await selectAsset('To Archive 2');
    await archiveSelectedAsset();
    await selectAsset('To Archive 1');
    await archiveSelectedAsset();

    // Active view: 1 asset
    await expect(
      page.getByRole('button', { name: 'Active One' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'To Archive 1' }),
    ).toBeHidden();

    // Toggle to archived
    await page.getByLabel('Show Archived').check();

    // Archived view: 2 assets with badges
    await expect(
      page.getByRole('button', { name: 'To Archive 1' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'To Archive 2' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Active One' }),
    ).toBeHidden();

    // Toggle back to active
    await page.getByLabel('Show Archived').uncheck();

    await expect(
      page.getByRole('button', { name: 'Active One' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'To Archive 1' }),
    ).toBeHidden();
  });

  test('toggle clears selected asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Selected Asset',
      category: 'other',
      location: 'Room',
    });

    // Select asset to open detail view
    await selectAsset('Selected Asset');
    await expect(
      page.getByText('Selected Asset', { exact: true }).first(),
    ).toBeVisible();

    // Toggle archived
    await page.getByLabel('Show Archived').check();

    // Detail view should close
    await expect(
      page.getByText('Selected Asset', { exact: true }).first(),
    ).toBeHidden();

    // Toggle back
    await page.getByLabel('Show Archived').uncheck();

    // No asset detail is selected
    await expect(
      page.getByText('Selected Asset', { exact: true }).first(),
    ).toBeHidden();
    // List item is visible
    await expect(
      page.getByRole('button', { name: 'Selected Asset' }),
    ).toBeVisible();
  });

  test('toggle closes create form', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    // Open create form
    await page.getByRole('button', { name: 'Add Asset' }).click();
    await expect(page.getByLabel('Name')).toBeVisible();

    // Toggle archived
    await page.getByLabel('Show Archived').check();

    // Create form should close
    await expect(page.getByLabel('Name')).toBeHidden();

    // Toggle back
    await page.getByLabel('Show Archived').uncheck();

    // Create form remains closed
    await expect(page.getByLabel('Name')).toBeHidden();
    // Empty state or list should be visible
    await expect(page.getByText('No assets yet.')).toBeVisible();
  });

  test('repeated toggle actions work correctly', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Active A', category: 'other', location: 'A' });
    await createAsset({ name: 'Active B', category: 'other', location: 'B' });
    await createAsset({ name: 'To Archive A', category: 'other', location: 'C' });
    await createAsset({ name: 'To Archive B', category: 'other', location: 'D' });

    // Archive 2
    await selectAsset('To Archive B');
    await archiveSelectedAsset();
    await selectAsset('To Archive A');
    await archiveSelectedAsset();

    // Rapid toggle back and forth
    for (let i = 0; i < 3; i++) {
      // Show archived
      await page.getByLabel('Show Archived').check();
      await expect(
        page.getByRole('button', { name: 'To Archive A' }),
      ).toBeVisible();
      await expect(
        page.getByRole('button', { name: 'To Archive B' }),
      ).toBeVisible();

      // Show active
      await page.getByLabel('Show Archived').uncheck();
      await expect(
        page.getByRole('button', { name: 'Active A' }),
      ).toBeVisible();
      await expect(
        page.getByRole('button', { name: 'Active B' }),
      ).toBeVisible();
    }
  });
});
