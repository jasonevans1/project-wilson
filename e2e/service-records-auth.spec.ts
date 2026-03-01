import { test, expect } from './service-records-fixtures';

test.describe('SR1 — Authentication & Access Control', () => {
  test('unauthenticated user visiting a service records URL is redirected to /login', async ({ page }) => {
    await page.goto('/assets/1/service-records');
    await page.waitForURL('**/login');

    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByText('Log in to your account')).toBeVisible();
    await expect(page.getByLabel('Email address')).toBeVisible();
  });

  test('authenticated user can reach a service records page from the asset detail panel', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'HVAC Unit', category: 'hvac', location: 'Basement' });
    await selectAsset('HVAC Unit');

    await expect(page.getByRole('link', { name: 'View Service Records' })).toBeVisible();

    await page.getByRole('link', { name: 'View Service Records' }).click();
    await page.waitForURL(/\/assets\/\d+\/service-records/);

    await expect(page.locator('[data-flux-heading]', { hasText: 'HVAC Unit — Service Records' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('button', { name: 'Add Service Record' })).toBeVisible();
    await expect(page.getByText('No service records yet.')).toBeVisible();
  });
});
