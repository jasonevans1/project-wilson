import { test, expect } from './fixtures';

test.describe('Asset Archiving', () => {
  test('archive an active asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Fridge to Archive',
      category: 'appliance',
      location: 'Kitchen',
    });

    await selectAsset('Fridge to Archive');

    // Verify Archive button is visible
    await expect(
      page.getByRole('button', { name: 'Archive' }),
    ).toBeVisible();

    // Click Archive to open modal
    await page.getByRole('button', { name: 'Archive' }).first().click();

    // Verify modal content
    await expect(
      page.getByText('Archive this asset?', { exact: true }),
    ).toBeVisible();
    await expect(
      page.getByText(
        'This asset will be moved to the archived list and will no longer appear in your active assets.',
      ),
    ).toBeVisible();

    // Confirm archive
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Archive' })
      .click();

    // Asset should no longer be in active list
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Toggle to archived view
    await page.getByLabel('Show Archived').check();

    // Archived asset should appear with badge
    await expect(
      page.getByRole('button', { name: /Fridge to Archive/ }),
    ).toBeVisible();
    // Verify the "Archived" badge is present (use exact and last to avoid "Show Archived" label)
    await expect(page.getByText('Archived', { exact: true }).last()).toBeVisible();
  });

  test('cancel archive confirmation', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Keep This Asset',
      category: 'hvac',
      location: 'Basement',
    });

    await selectAsset('Keep This Asset');

    // Open archive modal
    await page.getByRole('button', { name: 'Archive' }).first().click();
    await expect(
      page.getByText('Archive this asset?', { exact: true }),
    ).toBeVisible();

    // Cancel
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Cancel' })
      .click();

    // Modal should close, still in detail view
    await expect(
      page.getByText('Archive this asset?', { exact: true }),
    ).toBeHidden();
    await expect(
      page.getByText('Keep This Asset', { exact: true }).first(),
    ).toBeVisible();
  });

  test('close archive modal via close button', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Another Asset',
      category: 'plumbing',
      location: 'Bathroom',
    });

    await selectAsset('Another Asset');

    // Open archive modal
    await page.getByRole('button', { name: 'Archive' }).first().click();
    await expect(
      page.getByText('Archive this asset?', { exact: true }),
    ).toBeVisible();

    // Close via X button (Flux modal close button)
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Close' })
      .click();

    // Modal should close, still in detail view
    await expect(
      page.getByText('Archive this asset?', { exact: true }),
    ).toBeHidden();
    await expect(
      page.getByText('Another Asset', { exact: true }).first(),
    ).toBeVisible();
  });

  test('archive multiple assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    // Create 5 assets
    const names = ['Asset A', 'Asset B', 'Asset C', 'Asset D', 'Asset E'];
    for (const name of names) {
      await createAsset({ name, category: 'other', location: 'Storage' });
    }

    // Archive first 3
    for (const name of ['Asset E', 'Asset D', 'Asset C']) {
      await selectAsset(name);
      await archiveSelectedAsset();
    }

    // Active list should show 2 remaining
    await expect(
      page.getByRole('button', { name: 'Asset A' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Asset B' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Asset C' }),
    ).toBeHidden();

    // Toggle to archived
    await page.getByLabel('Show Archived').check();

    // 3 archived assets should be visible
    await expect(
      page.getByRole('button', { name: 'Asset C' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Asset D' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Asset E' }),
    ).toBeVisible();

    // Toggle back
    await page.getByLabel('Show Archived').uncheck();
    await expect(
      page.getByRole('button', { name: 'Asset A' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Asset B' }),
    ).toBeVisible();
  });
});
