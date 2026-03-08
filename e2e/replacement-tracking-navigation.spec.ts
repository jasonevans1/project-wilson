// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT7 — Navigation', () => {
  test('sidebar Replacement Tracking link navigates correctly', async ({
    page,
    registerAndGoToAssets,
  }) => {
    // Register (registerAndGoToAssets puts us on /assets)
    await registerAndGoToAssets();

    // Expect: 'Replacement Tracking' link visible in sidebar
    await expect(page.getByRole('link', { name: 'Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // Click the 'Replacement Tracking' link
    await page.getByRole('link', { name: 'Replacement Tracking' }).click();

    // Expect: URL is /replacement-tracking
    await page.waitForURL('**/replacement-tracking');
    await expect(page).toHaveURL(/\/replacement-tracking/);

    // Expect: 'Replacement Tracking' heading visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Replacement Tracking' })).toBeVisible({ timeout: 10000 });
  });

  test('navigating away and back preserves state', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    // Register, create 'Boiler', configure tracking
    await registerAndGoToAssets();
    await createAsset({ name: 'Boiler', category: 'hvac', location: 'Basement' });
    await configureTracking('Boiler', 15, '2020-01-01');

    // Navigate to replacement dashboard
    await navigateToReplacementDashboard();

    // Expect: 'Boiler' visible under 'Tracked Assets'
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Boiler' })).toBeVisible({ timeout: 10000 });

    // Click 'Assets' link in sidebar, wait for /assets URL
    await page.getByRole('link', { name: 'Assets' }).click();
    await page.waitForURL('**/assets');

    // Click 'Replacement Tracking' link again
    await page.getByRole('link', { name: 'Replacement Tracking' }).click();
    await page.waitForURL('**/replacement-tracking');

    // Expect: 'Boiler' still visible under 'Tracked Assets'
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-card]').filter({ hasText: 'Boiler' })).toBeVisible({ timeout: 10000 });
  });

  test('all sidebar links accessible from dashboard', async ({
    page,
    registerAndGoToAssets,
    navigateToReplacementDashboard,
  }) => {
    // Register, navigate to replacement dashboard
    await registerAndGoToAssets();
    await navigateToReplacementDashboard();

    // Expect: 'Dashboard' link visible, 'Assets' link visible, 'Replacement Tracking' link visible
    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('link', { name: 'Assets' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('link', { name: 'Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // Click 'Assets' link
    await page.getByRole('link', { name: 'Assets' }).click();

    // Expect: URL is /assets, page loads correctly
    await page.waitForURL('**/assets');
    await expect(page).toHaveURL(/\/assets/);
    await expect(page.getByText('Assets', { exact: true }).first()).toBeVisible({ timeout: 10000 });
  });
});
