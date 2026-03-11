// spec: tests/playwright/dashboard/assets-panel.spec.ts
// seed: e2e/seed.spec.ts

import { test, expect } from '@playwright/test';

async function login(page: any, email: string, password = 'password') {
  await page.goto('/login');
  await page.getByRole('textbox', { name: 'Email address' }).fill(email);
  await page.getByRole('textbox', { name: 'Password' }).fill(password);
  await page.locator('[data-test="login-button"]').click();
  await expect(page).toHaveURL('/dashboard');
}

async function registerNewUser(page: any, email: string, name = 'New User') {
  await page.goto('/register');
  await page.getByRole('textbox', { name: 'Name' }).fill(name);
  await page.getByRole('textbox', { name: 'Email address' }).fill(email);
  await page.getByRole('textbox', { name: 'Password', exact: true }).fill('password');
  await page.getByRole('textbox', { name: 'Confirm password' }).fill('password');
  await page.getByRole('button', { name: 'Create account' }).click();
  await expect(page).toHaveURL('/dashboard');
}

async function addAsset(page: any, assetName: string) {
  await page.goto('/assets');
  await page.getByRole('button', { name: 'Add Asset' }).click();
  await page.getByRole('textbox', { name: 'Name' }).fill(assetName);
  await page.getByRole('combobox', { name: 'Category' }).selectOption('Other');
  await page.getByRole('textbox', { name: 'Location' }).fill('Home');
  await page.getByRole('button', { name: 'Save' }).click();
  await expect(page.getByRole('button', { name: new RegExp(assetName) })).toBeVisible();
}

async function archiveAsset(page: any, assetName: string) {
  await page.goto('/assets');
  await page.getByRole('button', { name: new RegExp(assetName) }).click();
  // Wait for the detail panel to fully load and Alpine.js to initialize
  await page.waitForLoadState('networkidle');
  await page.getByRole('button', { name: 'Archive' }).click();
  // Wait for the confirmation dialog to open and confirm
  await page.locator('dialog[open]').waitFor();
  await page.locator('dialog[open]').getByRole('button', { name: 'Archive' }).click();
  // Wait for dialog to close and archive to complete before navigating
  await page.locator('dialog[open]').waitFor({ state: 'hidden' });
  await page.waitForLoadState('networkidle');
}

test.describe('Assets Panel', () => {
  test('Assets panel shows empty state when user has no assets', async ({ page }) => {
    // 1. Log in as a brand-new user with no assets and navigate to the dashboard
    const uniqueEmail = `assets-empty-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Assets panel (wrench icon, 'ASSETS' heading)
    // expect: The text 'No assets tracked yet.' is displayed
    await expect(page.getByText('No assets tracked yet.')).toBeVisible();
    // expect: No numeric count is shown
    await expect(page.getByText('active asset')).not.toBeVisible();
    // expect: A button labelled 'Add your first asset' is present
    await expect(page.getByRole('link', { name: 'Add your first asset' })).toBeVisible();

    // 3. Click the 'Add your first asset' button
    await page.getByRole('link', { name: 'Add your first asset' }).click();

    // expect: The browser navigates to /assets
    await expect(page).toHaveURL('/assets');
  });

  test('Assets panel shows active asset count with View assets CTA', async ({ page }) => {
    // 1. Log in as a user with active assets and navigate to the dashboard
    // test@example.com has 4 active assets
    await login(page, 'test@example.com');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Assets panel
    // expect: A large bold numeric count is displayed representing the number of active assets
    await expect(page.getByText('4')).toBeVisible();
    // expect: The label reads 'active assets'
    await expect(page.getByText('active assets')).toBeVisible();
    // expect: A button labelled 'View assets' is present
    await expect(page.getByRole('link', { name: 'View assets' }).first()).toBeVisible();

    // 3. Click the 'View assets' button
    await page.getByRole('link', { name: 'View assets' }).first().click();

    // expect: The browser navigates to /assets
    await expect(page).toHaveURL('/assets');
  });

  test('Assets panel uses singular label for exactly one active asset', async ({ page }) => {
    // 1. Log in as a user with exactly one active asset and navigate to the dashboard
    const uniqueEmail = `assets-singular-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add exactly one asset
    await addAsset(page, 'Test Asset');

    // Navigate to dashboard
    await page.goto('/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Assets panel count label
    // expect: The count shows '1'
    await expect(page.getByText('1', { exact: true })).toBeVisible();
    // expect: The label reads 'active asset' (singular), not 'active assets'
    await expect(page.getByText('active asset')).toBeVisible();
    await expect(page.getByText('active assets')).not.toBeVisible();
  });

  test('Assets panel excludes archived assets from count', async ({ page }) => {
    // 1. Log in as a user who has one active asset and one archived asset
    const uniqueEmail = `assets-archive-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add two assets
    await addAsset(page, 'Active Asset');
    await addAsset(page, 'Archived Asset');

    // Archive the second asset
    await archiveAsset(page, 'Archived Asset');

    // Navigate back to dashboard
    await page.goto('/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Assets panel count
    // expect: The count is '1', not '2' — archived asset is excluded
    await expect(page.getByText('active asset')).toBeVisible();
    await expect(page.getByText('No assets tracked yet.')).not.toBeVisible();
    // The count should be 1 (singular label)
    await expect(page.getByText('active assets')).not.toBeVisible();
  });
});
