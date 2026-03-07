// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT4 — Configure Replacement Tracking via Dashboard', () => {
  test('happy path: configure tracking on an untracked asset via the dashboard Set Up button', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    navigateToReplacementDashboard,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Boiler', category: 'hvac', location: 'Basement' });

    // 1. Navigate to dashboard and verify untracked state
    await navigateToReplacementDashboard();
    await expect(page.getByText('Not Yet Tracked')).toBeVisible();
    await expect(page.getByText('Boiler').first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible();

    // 2. Click Set Up on Boiler card
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    // HVAC default lifespan is 15
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('15');
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();

    // 3. Fill Installation Date and save
    await page.getByLabel('Installation Date').fill('2018-05-20');
    await page.getByRole('button', { name: 'Save' }).click();

    // Verify form gone and Boiler moved to Tracked Assets
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Boiler').first()).toBeVisible();
    // Should show expected replacement year (2018 + 15 = 2033)
    await expect(page.getByText('2033')).toBeVisible();
  });

  test('Cancel button on dashboard untracked Set Up form closes the form without saving', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    navigateToReplacementDashboard,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Pump', category: 'plumbing', location: 'Basement' });

    // Navigate to dashboard and open the Set Up form
    await navigateToReplacementDashboard();
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible();
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // Click Cancel
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Verify form gone, asset stays in Not Yet Tracked, Set Up button reappears
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Not Yet Tracked')).toBeVisible();
    await expect(page.getByText('Test Pump').first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible();
  });

  test('opening Set Up form closes any currently open Record Replacement form on the dashboard', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Furnace', category: 'hvac', location: 'Basement' });
    await configureTracking('Test Furnace', 10, '2020-01-01');

    // Navigate to dashboard
    await navigateToReplacementDashboard();
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Test Furnace').first()).toBeVisible();

    // 1. Open Record Replacement form
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // 2. Click Edit to open Setup form — Record Replacement form should close
    await page.getByRole('button', { name: 'Edit' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
  });

  test('dashboard Edit button on a tracked asset pre-fills the setup form with existing values', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Heat Pump', category: 'hvac', location: 'Attic' });
    await configureTracking('Heat Pump', 10, '2022-04-01');

    // Navigate to dashboard and click Edit
    await navigateToReplacementDashboard();
    await expect(page.getByText('Tracked Assets')).toBeVisible({ timeout: 10000 });
    await page.getByRole('button', { name: 'Edit' }).click();

    // Verify form shows with existing values
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('10');
    await expect(page.getByLabel('Installation Date')).toHaveValue('2022-04-01');
  });
});
