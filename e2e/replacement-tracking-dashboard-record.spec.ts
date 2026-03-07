// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT6 — Record Replacement from Dashboard', () => {
  test('happy path: record replacement from dashboard', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register, create Furnace, configure tracking with overdue install date
    await registerAndGoToAssets();
    await createAsset({ name: 'Furnace', category: 'hvac', location: 'Basement' });
    await configureTracking('Furnace', 10, '2010-01-01');

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Expect: 'Furnace' visible, 'Overdue' badge visible, 'Record Replacement' button visible
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Furnace' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Overdue', { exact: true })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('button', { name: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Click 'Record Replacement' on Furnace card
    await page.getByRole('button', { name: 'Record Replacement' }).click();

    // Expect: 'Record Replacement' form heading visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Expect: New Installation Date empty
    await expect(page.getByLabel('New Installation Date')).toHaveValue('');

    // Expect: Expected Lifespan pre-filled with '20'
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('10');

    // Expect: Save and Cancel visible
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();

    // Fill New Installation Date '2026-01-15' and click Save
    await page.getByLabel('New Installation Date').fill('2026-01-15');
    await page.getByRole('button', { name: 'Save' }).click();

    // Expect: form gone
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });

    // Expect: 'Overdue' badge NOT visible
    await expect(page.getByText('Overdue', { exact: true })).not.toBeVisible({ timeout: 10000 });

    // Expect: 'years remaining' text visible (instead of 'years overdue')
    await expect(page.getByText(/years remaining/)).toBeVisible({ timeout: 10000 });

    // Expect: progress bar inner div has bg-blue-500 class (not bg-red-500)
    const furnaceCard = page.locator('[data-flux-card]').filter({ hasText: 'Furnace' });
    await expect(furnaceCard.locator('.h-2.rounded-full.bg-blue-500')).toBeVisible({ timeout: 10000 });
    await expect(furnaceCard.locator('.h-2.rounded-full.bg-red-500')).not.toBeVisible();
  });

  test('opening Record Replacement form closes Edit form (mutual exclusivity)', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register, create asset, configure tracking
    await registerAndGoToAssets();
    await createAsset({ name: 'Heat Pump', category: 'hvac', location: 'Basement' });
    await configureTracking('Heat Pump', 10, '2020-01-01');

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Click 'Edit' button
    await page.getByRole('button', { name: 'Edit' }).click();

    // Expect: 'Configure Replacement Tracking' heading visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // Click 'Record Replacement' button
    await page.getByRole('button', { name: 'Record Replacement' }).click();

    // Expect: 'Configure Replacement Tracking' heading NOT visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });

    // Expect: 'Record Replacement' heading visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });
  });

  test('Cancel closes form, card unchanged', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register, create asset, configure tracking
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'appliance', location: 'Basement' });
    await configureTracking('Water Heater', 15, '2020-01-01');

    // Navigate to the replacement tracking dashboard
    await navigateToReplacementDashboard();

    // Click 'Record Replacement', fill some values, click 'Cancel'
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });
    await page.getByLabel('New Installation Date').fill('2025-06-01');
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Expect: 'Record Replacement' heading NOT visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });

    // Expect: 'Edit' and 'Record Replacement' buttons visible again
    await expect(page.getByRole('button', { name: 'Edit' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('button', { name: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Expect: card status unchanged (years remaining/overdue still shown)
    await expect(page.getByText(/years remaining|years overdue/)).toBeVisible({ timeout: 10000 });
  });
});
