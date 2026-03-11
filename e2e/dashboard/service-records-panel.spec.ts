// spec: tests/playwright/dashboard/service-records-panel.spec.ts
// seed: e2e/seed.spec.ts

import { test, expect } from '@playwright/test';

async function login(page: any, email: string, password = 'password') {
  await page.goto('https://project-wilson.ddev.site/login');
  await page.getByRole('textbox', { name: 'Email address' }).fill(email);
  await page.getByRole('textbox', { name: 'Password' }).fill(password);
  await page.locator('[data-test="login-button"]').click();
  await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');
}

async function registerNewUser(page: any, email: string, name = 'New User') {
  await page.goto('https://project-wilson.ddev.site/register');
  await page.getByRole('textbox', { name: 'Name' }).fill(name);
  await page.getByRole('textbox', { name: 'Email address' }).fill(email);
  await page.getByRole('textbox', { name: 'Password', exact: true }).fill('password');
  await page.getByRole('textbox', { name: 'Confirm password' }).fill('password');
  await page.getByRole('button', { name: 'Create account' }).click();
  await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');
}

async function addAsset(page: any, assetName: string) {
  await page.goto('https://project-wilson.ddev.site/assets');
  await page.getByRole('button', { name: 'Add Asset' }).click();
  await page.getByRole('textbox', { name: 'Name' }).fill(assetName);
  await page.getByRole('combobox', { name: 'Category' }).selectOption('Other');
  await page.getByRole('button', { name: 'Save' }).click();
}

async function addServiceRecord(page: any, assetName: string, serviceDate: string) {
  await page.goto('https://project-wilson.ddev.site/assets');
  await page.getByRole('button', { name: new RegExp(assetName) }).click();
  await page.getByRole('link', { name: 'View Service Records' }).click();
  await page.getByRole('button', { name: 'Add Service Record' }).click();
  await page.getByRole('textbox', { name: 'Service Date' }).fill(serviceDate);
  await page.getByRole('combobox', { name: 'Service Type' }).selectOption({ index: 1 });
  await page.getByRole('textbox', { name: 'Description' }).fill('Test service record');
  await page.getByRole('button', { name: 'Save Record' }).click();
}

test.describe('Service Records Panel', () => {
  test('Service Records panel shows empty state when no records exist', async ({ page }) => {
    // 1. Log in as a user with no service records and navigate to the dashboard
    const uniqueEmail = `sr-empty-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Service Records panel (clipboard icon, 'SERVICE RECORDS' heading)
    // expect: The text 'No service records logged yet.' is displayed
    await expect(page.getByText('No service records logged yet.')).toBeVisible();
    // expect: No asset name or date is shown
    await expect(page.getByText('Most recent service')).not.toBeVisible();
    // expect: A button labelled 'Log your first service record' is present
    await expect(page.getByRole('link', { name: 'Log your first service record' })).toBeVisible();

    // 3. Click the 'Log your first service record' button
    await page.getByRole('link', { name: 'Log your first service record' }).click();

    // expect: The browser navigates to https://project-wilson.ddev.site/assets
    await expect(page).toHaveURL('https://project-wilson.ddev.site/assets');
  });

  test('Service Records panel shows the most recent service record', async ({ page }) => {
    // 1. Log in as a user with multiple service records across different assets
    // test@example.com has a service record for HVAC Unit dated Feb 12, 2026
    await login(page, 'test@example.com');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Service Records panel
    // expect: The asset name of the most recently serviced asset is displayed
    await expect(page.getByText('HVAC Unit')).toBeVisible();
    // expect: The service date is shown in 'Mon D, YYYY' format
    await expect(page.getByText('Feb 12, 2026')).toBeVisible();
    // expect: The label 'Most recent service' is displayed below the date
    await expect(page.getByText('Most recent service')).toBeVisible();
    // expect: A button labelled 'View assets' is present
    await expect(page.getByRole('link', { name: 'View assets' }).first()).toBeVisible();

    // 3. Click the 'View assets' button
    await page.getByRole('link', { name: 'View assets' }).first().click();

    // expect: The browser navigates to https://project-wilson.ddev.site/assets
    await expect(page).toHaveURL('https://project-wilson.ddev.site/assets');
  });

  test("Service Records panel does not display another user's records", async ({ page }) => {
    // 1. Log in as User A who has no service records
    // User B (test@example.com) has service records
    const uniqueEmail = `sr-isolation-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed for User A
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Service Records panel
    // expect: 'No service records logged yet.' is displayed
    await expect(page.getByText('No service records logged yet.')).toBeVisible();
    // expect: User B's asset names and service dates are not visible
    await expect(page.getByText('HVAC Unit')).not.toBeVisible();
    await expect(page.getByText('Feb 12, 2026')).not.toBeVisible();
  });
});
