import { test, expect } from './fixtures';

test.describe('Pagination', () => {
  test('exactly 15 assets fit on one page without pagination', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    for (let i = 1; i <= 15; i++) {
      await createAsset({
        name: `Asset ${i}`,
        category: 'other',
        location: `Location ${i}`,
      });
    }

    // All 15 should be visible
    for (let i = 1; i <= 15; i++) {
      await expect(
        page.getByRole('button', { name: `Asset ${i} Other · Location ${i}` }),
      ).toBeVisible();
    }

    // No pagination controls
    await expect(page.locator('nav[role="navigation"][aria-label="Pagination Navigation"]')).toBeHidden();
  });

  test('more than 15 assets shows pagination', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    for (let i = 1; i <= 23; i++) {
      await createAsset({
        name: `Asset ${String(i).padStart(2, '0')}`,
        category: 'other',
        location: `Loc ${i}`,
      });
    }

    // Page 1 should show 15 assets
    const listButtons = page.locator(
      '[data-flux-card] button[type="button"]',
    );
    await expect(listButtons).toHaveCount(15);

    // Pagination should be visible
    await expect(page.locator('nav[role="navigation"][aria-label="Pagination Navigation"]')).toBeVisible();

    // Navigate to page 2
    await page.getByRole('button', { name: 'Go to page 2' }).click();

    // Page 2 should show remaining 8 assets
    await expect(listButtons).toHaveCount(8);

    // Navigate back to page 1
    await page.getByRole('button', { name: 'Go to page 1' }).click();
    await expect(listButtons).toHaveCount(15);
  });

  test('navigate between multiple pages', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    for (let i = 1; i <= 40; i++) {
      await createAsset({
        name: `Item ${String(i).padStart(2, '0')}`,
        category: 'other',
        location: `Loc ${i}`,
      });
    }

    const listButtons = page.locator(
      '[data-flux-card] button[type="button"]',
    );

    // Page 1: 15 assets
    await expect(listButtons).toHaveCount(15);

    // Go to page 2
    await page.getByRole('button', { name: 'Go to page 2' }).click();
    await expect(listButtons).toHaveCount(15);

    // Go to page 3
    await page.getByRole('button', { name: 'Go to page 3' }).click();
    await expect(listButtons).toHaveCount(10);

    // Go back to page 1
    await page.getByRole('button', { name: 'Go to page 1' }).click();
    await expect(listButtons).toHaveCount(15);
  });

  test('archived assets paginate separately', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    // Create 20 assets
    for (let i = 1; i <= 20; i++) {
      await createAsset({
        name: `Arch ${String(i).padStart(2, '0')}`,
        category: 'other',
        location: `Loc ${i}`,
      });
    }

    // Archive all 20 (they appear most recent first)
    for (let i = 20; i >= 1; i--) {
      await selectAsset(`Arch ${String(i).padStart(2, '0')}`);
      await archiveSelectedAsset();
    }

    // Active list should be empty
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Toggle to archived
    await page.getByLabel('Show Archived').check();

    const listButtons = page.locator(
      '[data-flux-card] button[type="button"]',
    );

    // Page 1: 15 archived assets
    await expect(listButtons).toHaveCount(15);

    // Navigate to page 2
    await page.getByRole('button', { name: 'Go to page 2' }).click();
    await expect(listButtons).toHaveCount(5);
  });

  test('pagination adjusts after archiving asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
    archiveSelectedAsset,
  }) => {
    await registerAndGoToAssets();

    // Create 16 assets (15 on page 1, 1 on page 2)
    for (let i = 1; i <= 16; i++) {
      await createAsset({
        name: `P ${String(i).padStart(2, '0')}`,
        category: 'other',
        location: `Loc ${i}`,
      });
    }

    const listButtons = page.locator(
      '[data-flux-card] button[type="button"]',
    );

    // Page 1 has 15, pagination exists
    await expect(listButtons).toHaveCount(15);
    await expect(page.locator('nav[role="navigation"][aria-label="Pagination Navigation"]')).toBeVisible();

    // Go to page 2
    await page.getByRole('button', { name: 'Go to page 2' }).click();
    await expect(listButtons).toHaveCount(1);

    // Archive the lone asset on page 2
    const assetButton = listButtons.first();
    const assetName = await assetButton.innerText();
    await assetButton.click();
    await archiveSelectedAsset();

    // Should be back on page 1 with 15 assets, no page 2
    await expect(listButtons).toHaveCount(15);
    await expect(page.locator('nav[role="navigation"][aria-label="Pagination Navigation"]')).toBeHidden();
  });
});
