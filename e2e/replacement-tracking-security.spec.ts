// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT8 — Security and Data Isolation', () => {
  test('user cannot see another user\'s assets on dashboard', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    navigateToReplacementDashboard,
    logout,
  }) => {
    // Register User A
    await registerAndGoToAssets({ name: 'User A' });

    // Create and configure tracking for 'User A Heater'
    await createAsset({ name: 'User A Heater', category: 'appliance', location: 'Basement' });
    await configureTracking('User A Heater', 10, '2020-01-01');

    // Logout User A
    await logout('User A');

    // Register User B
    await registerAndGoToAssets({ name: 'User B' });

    // Navigate to replacement dashboard as User B
    await navigateToReplacementDashboard();

    // Expect: empty state message visible
    await expect(
      page.getByText('No assets found. Add an asset to start tracking replacements.'),
    ).toBeVisible({ timeout: 10000 });

    // Expect: 'User A Heater' NOT visible
    await expect(page.getByText('User A Heater')).not.toBeVisible();
  });

  test('user cannot access another user\'s asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    navigateToReplacementDashboard,
    logout,
  }) => {
    // Register User A, create 'Secret Asset'
    await registerAndGoToAssets({ name: 'User A' });
    await createAsset({ name: 'Secret Asset', category: 'appliance', location: 'Basement' });

    // Logout User A
    await logout('User A');

    // Register User B
    await registerAndGoToAssets({ name: 'User B' });

    // On /assets page, expect 'Secret Asset' NOT visible
    await expect(page.getByText('Secret Asset')).not.toBeVisible();

    // Navigate to replacement dashboard
    await navigateToReplacementDashboard();

    // Expect: 'Secret Asset' NOT visible
    await expect(page.getByText('Secret Asset')).not.toBeVisible();
  });
});
