import { test, expect } from './fixtures';

test.describe('Performance & Load Testing', () => {
  test('page loads with many assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    // Create 30 assets (testing pagination with 2 pages)
    for (let i = 1; i <= 30; i++) {
      await createAsset({
        name: `Perf Asset ${String(i).padStart(2, '0')}`,
        category: 'other',
        location: `Location ${i}`,
      });
    }

    // Verify page 1 loads with 15 items
    const listButtons = page.locator(
      '[data-flux-card] button[type="button"]',
    );
    await expect(listButtons).toHaveCount(15);

    // Navigate to page 2
    await page.getByRole('button', { name: 'Go to page 2' }).click();
    await expect(listButtons).toHaveCount(15);

    // Navigate back quickly
    await page.getByRole('button', { name: 'Go to page 1' }).click();
    await expect(listButtons).toHaveCount(15);
  });

  test('large dataset with active and archived assets', async ({
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
        name: `Dataset ${String(i).padStart(2, '0')}`,
        category: 'other',
        location: `Loc ${i}`,
      });
    }

    // Archive 10 of them (most recent first)
    for (let i = 20; i >= 11; i--) {
      await selectAsset(`Dataset ${String(i).padStart(2, '0')}`);
      await archiveSelectedAsset();
    }

    // Active list: 10 assets (1 page)
    const listButtons = page.locator(
      '[data-flux-card] button[type="button"]',
    );
    await expect(listButtons).toHaveCount(10);

    // Toggle to archived: 10 assets (1 page)
    await page.getByLabel('Show Archived').check();
    await expect(listButtons).toHaveCount(10);

    // Click through a few assets to verify detail views load
    await selectAsset('Dataset 11');
    await expect(
      page.getByText('Dataset 11', { exact: true }).first(),
    ).toBeVisible();
    await page.getByRole('button', { name: 'Back' }).click();

    await selectAsset('Dataset 15');
    await expect(
      page.getByText('Dataset 15', { exact: true }).first(),
    ).toBeVisible();
    await page.getByRole('button', { name: 'Back' }).click();

    // Toggle back to active
    await page.getByLabel('Show Archived').uncheck();
    await selectAsset('Dataset 05');
    await expect(
      page.getByText('Dataset 05', { exact: true }).first(),
    ).toBeVisible();
  });
});
