import { test, expect } from './fixtures';

test.describe('UI Responsiveness & Real-time Updates', () => {
  test('success banner auto-dismisses after 3 seconds', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Banner Test',
      category: 'other',
      location: 'Room',
    });

    // Banner should be visible immediately
    const banner = page.getByText('Asset added.');
    await expect(banner).toBeVisible();

    // Wait for auto-dismiss (3 seconds + transition time)
    await expect(banner).toBeHidden({ timeout: 5000 });
  });

  test('list updates after creating assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    // Start with empty state
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Create first asset
    await createAsset({
      name: 'First Asset',
      category: 'appliance',
      location: 'Kitchen',
    });

    // Empty state should be gone
    await expect(page.getByText('No assets yet.')).toBeHidden();
    await expect(
      page.getByRole('button', { name: 'First Asset' }),
    ).toBeVisible();

    // Wait to ensure different timestamps due to MySQL second precision
    await page.waitForTimeout(1100);

    // Create second asset
    await createAsset({
      name: 'Second Asset',
      category: 'hvac',
      location: 'Basement',
    });

    // Both visible, second at top (most recent)
    await expect(
      page.getByRole('button', { name: 'Second Asset' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'First Asset' }),
    ).toBeVisible();

    // Check that Second Asset is first in the list (most recent)
    const firstItem = page
      .locator('.divide-y button')
      .first();
    await expect(firstItem).toContainText('Second Asset');
  });

  test('list updates after archiving', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Keep Me', category: 'other', location: 'A' });
    await createAsset({ name: 'Remove Me', category: 'other', location: 'B' });
    await createAsset({ name: 'Also Keep', category: 'other', location: 'C' });

    // Archive one
    await selectAsset('Remove Me');
    await archiveSelectedAsset();

    // Only 2 remain in active list
    await expect(
      page.getByRole('button', { name: 'Keep Me' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Also Keep' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Remove Me' }),
    ).toBeHidden();

    // Archived view shows the archived asset
    await page.getByLabel('Show Archived').check();
    await expect(
      page.getByRole('button', { name: 'Remove Me' }),
    ).toBeVisible();
  });

  test('list updates after restoring', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({ name: 'Restore Me', category: 'other', location: 'A' });
    await createAsset({ name: 'Also Restore', category: 'other', location: 'B' });

    // Archive both
    await selectAsset('Also Restore');
    await archiveSelectedAsset();
    await selectAsset('Restore Me');
    await archiveSelectedAsset();

    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Switch to archived
    await page.getByLabel('Show Archived').check();

    // Restore one
    await selectAsset('Restore Me');
    await page.getByRole('button', { name: 'Restore' }).click();

    // Only 1 left in archived
    await expect(
      page.getByRole('button', { name: 'Also Restore' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Restore Me' }),
    ).toBeHidden();

    // Switch to active
    await page.getByLabel('Show Archived').uncheck();

    // Restored asset visible
    await expect(
      page.getByRole('button', { name: 'Restore Me' }),
    ).toBeVisible();
  });

  test('Livewire events update UI correctly through full workflow', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    // Create: asset-created event shows banner
    await createAsset({
      name: 'Workflow Asset',
      category: 'appliance',
      location: 'Kitchen',
    });
    await expect(page.getByText('Asset added.')).toBeVisible();

    // Select and Edit: asset-updated event refreshes detail
    await selectAsset('Workflow Asset');
    await page.getByRole('button', { name: 'Edit' }).click();
    await page.getByLabel('Name').fill('Updated Workflow');
    await page.getByRole('button', { name: 'Save' }).click();
    await expect(
      page.getByText('Updated Workflow', { exact: true }).first(),
    ).toBeVisible();

    // Archive: asset-archived event clears selection
    await archiveSelectedAsset();
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Restore: asset-restored event clears selection
    await page.getByLabel('Show Archived').check();
    await selectAsset('Updated Workflow');
    await page.getByRole('button', { name: 'Restore' }).click();
    // Archived list should be empty after restore
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Switch back to active - restored asset visible
    await page.getByLabel('Show Archived').uncheck();
    await expect(
      page.getByRole('button', { name: 'Updated Workflow' }),
    ).toBeVisible();

    // Close panel: close-panel event closes detail
    await selectAsset('Updated Workflow');
    await page.getByRole('button', { name: 'Back' }).click();
    // Detail view should close - check Back button is gone
    await expect(page.getByRole('button', { name: 'Back' })).toBeHidden();
    // List should be visible
    await expect(
      page.getByRole('button', { name: 'Updated Workflow' }),
    ).toBeVisible();
  });
});
