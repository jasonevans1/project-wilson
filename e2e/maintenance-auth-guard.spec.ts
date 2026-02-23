import { test, expect } from './maintenance-fixtures';

test.describe('Authentication Guard', () => {
  test('unauthenticated user visiting /maintenance is redirected to the login page', async ({ page }) => {
    await page.goto('/maintenance');
    await page.waitForURL('**/login');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByText('Log in to your account')).toBeVisible();
    await expect(page.getByLabel('Email address')).toBeVisible();
  });

  test('unauthenticated user visiting /assets/1/maintenance is redirected to the login page', async ({ page }) => {
    await page.goto('/assets/1/maintenance');
    await page.waitForURL('**/login');

    await expect(page).toHaveURL(/\/login/);
  });

  test('authenticated user can reach /maintenance via the sidebar Maintenance nav link', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Assets' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Maintenance' })).toBeVisible();

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Maintenance Schedule')).toBeVisible();
    await expect(page.getByRole('combobox')).toBeVisible();
  });
});
