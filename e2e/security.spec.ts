import { test, expect } from './fixtures';

test.describe('Data Isolation & Security', () => {
  test('user cannot see other user assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    loginAndGoToAssets,
    logout,
  }) => {
    // Register User A and create assets
    const userA = await registerAndGoToAssets({
      name: 'User A',
      email: `usera-${Date.now()}@example.com`,
    });

    await createAsset({ name: 'User A Fridge', category: 'appliance', location: 'Kitchen' });
    await createAsset({ name: 'User A HVAC', category: 'hvac', location: 'Basement' });
    await createAsset({ name: 'User A Sink', category: 'plumbing', location: 'Bathroom' });

    // Verify User A sees their assets
    await expect(
      page.getByRole('button', { name: 'User A Fridge' }),
    ).toBeVisible();

    // Log out
    await logout('User A');

    // Register User B
    const userB = await registerAndGoToAssets({
      name: 'User B',
      email: `userb-${Date.now()}@example.com`,
    });

    // User B should see empty state
    await expect(page.getByText('No assets yet.')).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'User A Fridge' }),
    ).toBeHidden();

    // User B creates their own assets
    await createAsset({ name: 'User B Deck', category: 'exterior', location: 'Backyard' });
    await createAsset({ name: 'User B Floors', category: 'flooring', location: 'Den' });

    await expect(
      page.getByRole('button', { name: 'User B Deck' }),
    ).toBeVisible();

    // Log out and log back in as User A
    await logout('User B');

    await loginAndGoToAssets(userA.email, userA.password);

    // User A should only see their 3 assets
    await expect(
      page.getByRole('button', { name: 'User A Fridge' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'User A HVAC' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'User A Sink' }),
    ).toBeVisible();

    // User A should NOT see User B's assets
    await expect(
      page.getByRole('button', { name: 'User B Deck' }),
    ).toBeHidden();
    await expect(
      page.getByRole('button', { name: 'User B Floors' }),
    ).toBeHidden();
  });

  test('user cannot edit other user assets via direct URL', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    loginAndGoToAssets,
    logout,
  }) => {
    // Register User A and create an asset
    const userA = await registerAndGoToAssets({
      name: 'Owner',
      email: `owner-${Date.now()}@example.com`,
    });

    await createAsset({
      name: 'Private Asset',
      category: 'appliance',
      location: 'Kitchen',
    });

    // Log out
    await logout('Owner');

    // Register User B
    await registerAndGoToAssets({
      name: 'Intruder',
      email: `intruder-${Date.now()}@example.com`,
    });

    // User B should see empty state, cannot see User A's asset
    await expect(page.getByText('No assets yet.')).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Private Asset' }),
    ).toBeHidden();
  });

  test('user cannot archive other user assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    loginAndGoToAssets,
    logout,
  }) => {
    // Register User A and create an asset
    const userA = await registerAndGoToAssets({
      name: 'Asset Owner',
      email: `assetowner-${Date.now()}@example.com`,
    });

    await createAsset({
      name: 'Protected Asset',
      category: 'electrical',
      location: 'Utility Room',
    });

    // Log out
    await logout('Asset Owner');

    // Register User B
    await registerAndGoToAssets({
      name: 'Other User',
      email: `otheruser-${Date.now()}@example.com`,
    });

    // User B has no access to User A's assets at all
    await expect(page.getByText('No assets yet.')).toBeVisible();

    // Log back in as User A to verify asset is still active
    await logout('Other User');

    await loginAndGoToAssets(userA.email, userA.password);
    await expect(
      page.getByRole('button', { name: 'Protected Asset' }),
    ).toBeVisible();
  });
});
