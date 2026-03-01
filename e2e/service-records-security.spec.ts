import { test, expect } from './service-records-fixtures';

test.describe('SR8 — Data Isolation', () => {
  test('user cannot see service records belonging to another user\'s asset', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetServiceRecords,
    createServiceRecord,
    logout,
  }) => {
    // Register User A, create an asset and a service record
    await registerAndGoToAssets({ name: 'User A', email: `usera-${Date.now()}@example.com` });
    await createAsset({ name: 'User A Appliance', category: 'appliance', location: 'Kitchen' });
    await goToAssetServiceRecords('User A Appliance');
    await createServiceRecord({ date: '2025-06-01', type: 'repair', description: 'User A private record' });
    await expect(page.getByText('User A private record')).toBeVisible();

    // Capture User A's service records URL
    const userAServiceRecordsUrl = page.url();

    // Log out as User A
    await logout('User A');

    // Register User B
    await registerAndGoToAssets({ name: 'User B', email: `userb-${Date.now()}@example.com` });
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Navigate directly to User A's service records URL
    const response = await page.goto(userAServiceRecordsUrl);

    // Server returns 403 Forbidden
    expect(response?.status()).toBe(403);
  });
});
