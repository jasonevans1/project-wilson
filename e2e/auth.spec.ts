import { test, expect } from './fixtures';

test.describe('Authentication & Access Control', () => {
  test('unauthenticated user is redirected to login', async ({ page }) => {
    await page.goto('/assets');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByLabel('Email address')).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Password' })).toBeVisible();
  });

  test('user can register and access assets page', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await expect(page).toHaveURL(/\/assets/);
    await expect(page.getByText('Assets', { exact: true }).first()).toBeVisible();
    await expect(page.getByText('No assets yet.')).toBeVisible();
    await expect(
      page.getByText('Click "Add Asset" to add your first home asset.'),
    ).toBeVisible();
  });

  test('user can log in and access assets page', async ({
    page,
    registerAndGoToAssets,
    loginAndGoToAssets,
  }) => {
    // First register to create the user
    const { email, password } = await registerAndGoToAssets();

    // Log out
    await page.getByRole('button', { name: 'TU Test User' }).click();
    await page.getByRole('menuitem', { name: 'Log Out' }).click();
    await page.waitForURL(/\/$/); // Redirects to home page after logout

    // Log back in
    await loginAndGoToAssets(email, password);

    await expect(page).toHaveURL(/\/assets/);
    await expect(page.getByText('Assets', { exact: true }).first()).toBeVisible();
  });
});
