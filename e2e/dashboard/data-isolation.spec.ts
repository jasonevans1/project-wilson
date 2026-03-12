// spec: tests/playwright/dashboard/data-isolation.spec.ts
// seed: e2e/seed.spec.ts

import { test, expect } from '@playwright/test';

async function registerNewUser(page: any, email: string, name = 'New User') {
  await page.goto('/register');
  await page.getByRole('textbox', { name: 'Name' }).fill(name);
  await page.getByRole('textbox', { name: 'Email address' }).fill(email);
  await page.getByRole('textbox', { name: 'Password', exact: true }).fill('password');
  await page.getByRole('textbox', { name: 'Confirm password' }).fill('password');
  await page.getByRole('button', { name: 'Create account' }).click();
  await expect(page).toHaveURL('/dashboard');
}

test.describe('Cross-Panel Data Isolation', () => {
  test("Dashboard shows only the authenticated user's data across all four panels", async ({ page }) => {
    // 1. Ensure User B (test@example.com) has assets, overdue maintenance, service records,
    //    and assets approaching replacement. Log in as User A who has no data.
    const uniqueEmail = `isolation-userA-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed for User A
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect all four dashboard panels
    // expect: Assets panel: 'No assets tracked yet.'
    await expect(page.getByText('No assets tracked yet.')).toBeVisible();
    // expect: Maintenance panel: 'No maintenance tasks scheduled yet.'
    await expect(page.getByText('No maintenance tasks scheduled yet.')).toBeVisible();
    // expect: Service Records panel: 'No service records logged yet.'
    await expect(page.getByText('No service records logged yet.')).toBeVisible();
    // expect: Replacement Tracking panel: 'No replacement timelines configured.'
    await expect(page.getByText('No replacement timelines configured.')).toBeVisible();
    // expect: No data belonging to User B appears anywhere on the page
    await expect(page.getByText('HVAC Unit')).not.toBeVisible();
    await expect(page.getByText('overdue')).not.toBeVisible();
    await expect(page.getByText('attention')).not.toBeVisible();
  });

  test('Dashboard data refreshes to reflect new assets after they are added', async ({ page }) => {
    // 1. Log in as a new user with no assets and navigate to the dashboard
    const uniqueEmail = `isolation-refresh-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The Assets panel shows 'No assets tracked yet.' and the 'Add your first asset' button
    await expect(page.getByText('No assets tracked yet.')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Add your first asset' })).toBeVisible();

    // 2. Click the 'Add your first asset' button to navigate to the assets page
    await page.getByRole('link', { name: 'Add your first asset' }).click();

    // expect: The assets page is displayed
    await expect(page).toHaveURL('/assets');

    // 3. Create a new asset through the assets page interface
    await page.getByRole('button', { name: 'Add Asset' }).click();
    await page.getByRole('textbox', { name: 'Name' }).fill('My New Asset');
    await page.getByRole('combobox', { name: 'Category' }).selectOption('Other');
    await page.getByRole('textbox', { name: 'Location' }).fill('Home');
    await page.getByRole('button', { name: 'Save' }).click();

    // expect: The new asset is saved successfully
    await expect(page.getByRole('button', { name: /My New Asset/ })).toBeVisible();

    // 4. Navigate back to the dashboard at /dashboard
    await page.goto('/dashboard');

    // expect: The Assets panel now shows a count of '1'
    await expect(page.getByText('1', { exact: true })).toBeVisible();
    // expect: The label reads 'active asset'
    await expect(page.getByText('active asset')).toBeVisible();
    // expect: The CTA button now reads 'View assets' instead of 'Add your first asset'
    await expect(page.getByRole('link', { name: 'View assets' }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'Add your first asset' })).not.toBeVisible();
  });
});
