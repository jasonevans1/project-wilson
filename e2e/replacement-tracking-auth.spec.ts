// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT1 — Authentication and Access Control', () => {
  test('unauthenticated user visiting /replacement-tracking is redirected to /login', async ({ page }) => {
    // 1. Without logging in, navigate directly to /replacement-tracking
    await page.goto('/replacement-tracking');
    await page.waitForURL('**/login');

    // expect: The browser is redirected to /login
    await expect(page).toHaveURL(/\/login/);

    // expect: The heading 'Log in to your account' is visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Log in to your account' })).toBeVisible({ timeout: 10000 });

    // expect: The page shows a login form with Email address and Password fields
    await expect(page.getByLabel('Email address')).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Password' })).toBeVisible();
  });

  test('authenticated user can reach /replacement-tracking via the sidebar Replacement Tracking link', async ({
    page,
    registerAndGoToAssets,
  }) => {
    // 1. Register a new user and navigate to /dashboard
    await registerAndGoToAssets();

    // expect: The authenticated dashboard is visible
    // expect: The sidebar shows a 'Replacement Tracking' navigation link
    await expect(page.getByRole('link', { name: 'Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // 2. Click the 'Replacement Tracking' link in the sidebar
    await page.getByRole('link', { name: 'Replacement Tracking' }).click();
    await page.waitForURL('**/replacement-tracking');

    // expect: The browser navigates to /replacement-tracking
    await expect(page).toHaveURL(/\/replacement-tracking/);

    // expect: The page heading 'Replacement Tracking' is visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Replacement Tracking' })).toBeVisible({ timeout: 10000 });
  });

  test('authenticated user sees replacement tracking section in asset detail panel', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    // 1. Register a new user, create an asset 'Test Heater' (appliance, Basement), click on it
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Heater', category: 'appliance', location: 'Basement' });
    await selectAsset('Test Heater');

    // expect: The asset detail panel opens
    await expect(page.getByRole('button', { name: 'Back' })).toBeVisible();

    // expect: A 'Replacement' section heading is visible within the panel
    await expect(page.getByText('Replacement', { exact: true })).toBeVisible();

    // expect: The text 'Replacement tracking not yet configured.' is visible
    await expect(page.getByText('Replacement tracking not yet configured.')).toBeVisible({ timeout: 10000 });

    // expect: A 'Set Up' button is visible in the Replacement section
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible();
  });
});
