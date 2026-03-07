// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT2 — Dashboard States and Sorting', () => {
  test('dashboard shows empty state when user has no assets', async ({
    page,
    registerAndGoToAssets,
  }) => {
    // Register a new user with no assets
    await registerAndGoToAssets();

    // Navigate to the replacement tracking dashboard
    await page.getByRole('link', { name: 'Replacement Tracking' }).click();
    await page.waitForURL('**/replacement-tracking');

    // Expect: The page heading 'Replacement Tracking' is visible
    await expect(
      page.locator('[data-flux-heading]', { hasText: 'Replacement Tracking' }),
    ).toBeVisible({ timeout: 10000 });

    // Expect: The empty state message is visible
    await expect(
      page.getByText('No assets found. Add an asset to start tracking replacements.'),
    ).toBeVisible({ timeout: 10000 });

    // Expect: No 'Tracked Assets' section heading is shown
    await expect(page.getByText('Tracked Assets')).not.toBeVisible();

    // Expect: No 'Not Yet Tracked' section heading is shown
    await expect(page.getByText('Not Yet Tracked')).not.toBeVisible();
  });

  test('dashboard shows untracked asset in Not Yet Tracked section when no tracking is configured', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    navigateToReplacementDashboard,
  }) => {
    // Register a new user and create an untracked asset
    await registerAndGoToAssets();
    await createAsset({ name: 'Ceiling Fan', category: 'other', location: 'Living Room' });

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: The 'Not Yet Tracked' section heading is visible
    await expect(page.getByText('Not Yet Tracked')).toBeVisible({ timeout: 10000 });

    // Expect: A card for 'Ceiling Fan' is visible
    await expect(
      page.locator('[data-flux-card]').filter({ hasText: 'Ceiling Fan' }),
    ).toBeVisible({ timeout: 10000 });

    // Expect: The card shows 'Other · Living Room'
    await expect(page.getByText('Other · Living Room')).toBeVisible({ timeout: 10000 });

    // Expect: A 'Set Up' button is visible on the card
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible({ timeout: 10000 });

    // Expect: No 'Tracked Assets' section is shown
    await expect(page.getByText('Tracked Assets')).not.toBeVisible();
  });

  test('dashboard shows tracked asset in Tracked Assets section after tracking is configured', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register, create 'Water Heater', and configure replacement tracking
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'appliance', location: 'Basement' });
    await configureTracking('Water Heater', 10, '2020-01-15');

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: The 'Tracked Assets' section heading is visible
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });

    // Expect: A card for 'Water Heater' is visible
    await expect(
      page.locator('[data-flux-card]').filter({ hasText: 'Water Heater' }),
    ).toBeVisible({ timeout: 10000 });

    // Expect: The card shows 'Appliance · Basement'
    await expect(page.getByText('Appliance · Basement')).toBeVisible({ timeout: 10000 });

    // Expect: The card shows Expected Replacement year '2030'
    await expect(page.getByText('2030')).toBeVisible({ timeout: 10000 });

    // Expect: The 'Status' shows 'years remaining'
    await expect(page.getByText(/years remaining/)).toBeVisible({ timeout: 10000 });

    // Expect: An 'Edit' button is visible on the card
    await expect(page.getByRole('button', { name: 'Edit' })).toBeVisible({ timeout: 10000 });

    // Expect: A 'Record Replacement' button is visible on the card
    await expect(page.getByRole('button', { name: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Expect: No 'Not Yet Tracked' section is shown
    await expect(page.getByText('Not Yet Tracked')).not.toBeVisible();
  });

  test('dashboard shows both tracked and untracked sections when the user has a mix of assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register and create 'Water Heater' with tracking configured
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'appliance', location: 'Basement' });
    await configureTracking('Water Heater', 10, '2020-01-15');

    // Create 'HVAC Unit' without tracking
    await createAsset({ name: 'HVAC Unit', category: 'hvac', location: 'Attic' });

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: The 'Tracked Assets' section heading is visible
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });

    // Expect: A card for 'Water Heater' appears under 'Tracked Assets'
    await expect(
      page.locator('[data-flux-card]').filter({ hasText: 'Water Heater' }),
    ).toBeVisible({ timeout: 10000 });

    // Expect: The 'Not Yet Tracked' section heading is visible
    await expect(page.getByText('Not Yet Tracked')).toBeVisible({ timeout: 10000 });

    // Expect: A card for 'HVAC Unit' appears under 'Not Yet Tracked'
    await expect(
      page.locator('[data-flux-card]').filter({ hasText: 'HVAC Unit' }),
    ).toBeVisible({ timeout: 10000 });
  });

  test('overdue asset shows a red Overdue badge and red progress bar on the dashboard', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register, create 'Old Furnace', and configure tracking with past-due install date
    await registerAndGoToAssets();
    await createAsset({ name: 'Old Furnace', category: 'hvac', location: 'Basement' });
    await configureTracking('Old Furnace', 5, '2010-01-01');

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: The 'Tracked Assets' section shows 'Old Furnace'
    await expect(
      page.locator('[data-flux-card]').filter({ hasText: 'Old Furnace' }),
    ).toBeVisible({ timeout: 10000 });

    // Expect: A red 'Overdue' badge is visible on the 'Old Furnace' card
    await expect(page.getByText('Overdue', { exact: true })).toBeVisible({ timeout: 10000 });

    // Expect: The 'Status' field shows 'years overdue'
    await expect(page.getByText(/years overdue/)).toBeVisible({ timeout: 10000 });

    // Expect: The progress bar container is in the overdue state (bg-red-500 class on inner div)
    const overdueCard = page.locator('[data-flux-card]').filter({ hasText: 'Old Furnace' });
    await expect(overdueCard.locator('.h-2.rounded-full.bg-red-500')).toBeAttached({ timeout: 10000 });
  });

  test('untracked assets are sorted alphabetically on the dashboard', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    navigateToReplacementDashboard,
  }) => {
    // Register and create three untracked assets in non-alphabetical order
    await registerAndGoToAssets();
    await createAsset({ name: 'Zebra Fan', category: 'other', location: 'Hall' });
    await createAsset({ name: 'Apple Pump', category: 'plumbing', location: 'Basement' });
    await createAsset({ name: 'Mango Unit', category: 'hvac', location: 'Attic' });

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: The 'Not Yet Tracked' section shows all three assets
    await expect(page.getByText('Not Yet Tracked')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Apple Pump' })).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Mango Unit' })).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Zebra Fan' })).toBeVisible({ timeout: 10000 });

    // Verify DOM order: Apple Pump before Mango Unit before Zebra Fan
    const cardNames = await page.evaluate(() => {
      const cards = document.querySelectorAll('[data-flux-card]');
      const names: string[] = [];
      cards.forEach(card => {
        const heading = card.querySelector('[data-flux-heading]');
        if (heading) {
          names.push(heading.textContent?.trim() ?? '');
        }
      });
      return names;
    });

    const appleIndex = cardNames.indexOf('Apple Pump');
    const mangoIndex = cardNames.indexOf('Mango Unit');
    const zebraIndex = cardNames.indexOf('Zebra Fan');

    expect(appleIndex).toBeGreaterThanOrEqual(0);
    expect(mangoIndex).toBeGreaterThanOrEqual(0);
    expect(zebraIndex).toBeGreaterThanOrEqual(0);
    expect(appleIndex).toBeLessThan(mangoIndex);
    expect(mangoIndex).toBeLessThan(zebraIndex);
  });

  test('tracked assets are sorted by days remaining ascending (soonest replacement first) on the dashboard', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register and create 'Near Asset' with 5-year lifespan from 2022 (replaces ~2027)
    await registerAndGoToAssets();
    await createAsset({ name: 'Near Asset', category: 'appliance', location: 'Kitchen' });
    await configureTracking('Near Asset', 5, '2022-01-01');

    // Create 'Far Asset' with 20-year lifespan from 2020 (replaces ~2040)
    await createAsset({ name: 'Far Asset', category: 'appliance', location: 'Basement' });
    await configureTracking('Far Asset', 20, '2020-01-01');

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: Both assets appear under 'Tracked Assets'
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Near Asset' })).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Far Asset' })).toBeVisible({ timeout: 10000 });

    // Verify DOM order: Near Asset (soonest) appears before Far Asset
    const cardNames = await page.evaluate(() => {
      const cards = document.querySelectorAll('[data-flux-card]');
      const names: string[] = [];
      cards.forEach(card => {
        const heading = card.querySelector('[data-flux-heading]');
        if (heading) {
          names.push(heading.textContent?.trim() ?? '');
        }
      });
      return names;
    });

    const nearIndex = cardNames.indexOf('Near Asset');
    const farIndex = cardNames.indexOf('Far Asset');

    expect(nearIndex).toBeGreaterThanOrEqual(0);
    expect(farIndex).toBeGreaterThanOrEqual(0);
    expect(nearIndex).toBeLessThan(farIndex);
  });
});
