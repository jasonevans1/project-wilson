import { test, expect } from './fixtures';

test.describe('Asset Restoration', () => {
  test('restore an archived asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Asset to Restore',
      category: 'appliance',
      location: 'Kitchen',
    });

    // Archive it
    await selectAsset('Asset to Restore');
    await archiveSelectedAsset();

    // Active list should be empty
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Switch to archived view
    await page.getByLabel('Show Archived').check();
    await expect(
      page.getByRole('button', { name: 'Asset to Restore' }),
    ).toBeVisible();

    // Open archived asset detail
    await selectAsset('Asset to Restore');

    // Should show Restore button instead of Archive
    await expect(
      page.getByRole('button', { name: 'Restore' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Archive' }),
    ).toBeHidden();

    // Restore it
    await page.getByRole('button', { name: 'Restore' }).click();

    // Asset should no longer be in archived list
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Switch back to active
    await page.getByLabel('Show Archived').uncheck();

    // Asset should be back in active list
    await expect(
      page.getByRole('button', { name: 'Asset to Restore' }),
    ).toBeVisible();
  });

  test('restore multiple archived assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    // Create and archive 4 assets
    for (const name of ['Alpha', 'Beta', 'Gamma', 'Delta']) {
      await createAsset({ name, category: 'other', location: 'Storage' });
    }

    // Archive all (most recent first in list)
    for (const name of ['Delta', 'Gamma', 'Beta', 'Alpha']) {
      await selectAsset(name);
      await archiveSelectedAsset();
    }

    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Switch to archived
    await page.getByLabel('Show Archived').check();

    // Restore 2 of them
    await selectAsset('Alpha');
    await page.getByRole('button', { name: 'Restore' }).click();

    await selectAsset('Beta');
    await page.getByRole('button', { name: 'Restore' }).click();

    // 2 remaining archived
    await expect(
      page.getByRole('button', { name: 'Gamma' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Delta' }),
    ).toBeVisible();

    // Switch to active
    await page.getByLabel('Show Archived').uncheck();

    // 2 restored assets visible
    await expect(
      page.getByRole('button', { name: 'Alpha' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Beta' }),
    ).toBeVisible();
  });

  test('edit archived asset then restore', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Old Name',
      category: 'hvac',
      location: 'Basement',
    });

    // Archive it
    await selectAsset('Old Name');
    await archiveSelectedAsset();

    // Switch to archived
    await page.getByLabel('Show Archived').check();

    // Edit the archived asset
    await selectAsset('Old Name');
    await page.getByRole('button', { name: 'Edit' }).click();
    await page.getByLabel('Name').fill('Updated Name');
    await page.getByRole('button', { name: 'Save' }).click();

    // Verify update in detail view
    await expect(
      page.getByText('Updated Name', { exact: true }).first(),
    ).toBeVisible();

    // Restore it
    await page.getByRole('button', { name: 'Restore' }).click();

    // Switch to active
    await page.getByLabel('Show Archived').uncheck();

    // Restored with updated name
    await expect(
      page.getByRole('button', { name: 'Updated Name' }),
    ).toBeVisible();
  });
});
