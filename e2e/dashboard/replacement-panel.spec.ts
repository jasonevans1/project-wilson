// spec: tests/playwright/dashboard/replacement-panel.spec.ts
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

async function setupReplacementTracking(page: any, assetName: string, installDate: string, lifespanYears: string) {
  await page.goto('/assets');
  await page.getByRole('button', { name: new RegExp(assetName) }).click();
  await page.getByRole('button', { name: 'Set Up' }).click();
  await page.getByLabel('Expected Lifespan (Years)').fill(lifespanYears);
  await page.getByLabel('Installation Date').fill(installDate);
  await page.getByRole('button', { name: 'Save' }).click();
  await expect(page.getByText('Expected replacement:')).toBeVisible();
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
}

test.describe('Replacement Tracking Panel', () => {
  test('Replacement Tracking panel shows empty state when no assets have tracking configured', async ({ page }) => {
    // 1. Log in as a user with no assets that have install_date and expected_lifespan_years set
    const uniqueEmail = `repl-empty-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Replacement Tracking panel (arrow-path icon, 'REPLACEMENT TRACKING' heading)
    // expect: The text 'No replacement timelines configured.' is displayed
    await expect(page.getByText('No replacement timelines configured.')).toBeVisible();
    // expect: No count or attention label is shown
    await expect(page.getByText('attention')).not.toBeVisible();
    // expect: A button labelled 'Set up replacement tracking' is present
    await expect(page.getByRole('link', { name: 'Set up replacement tracking' })).toBeVisible();

    // 3. Click the 'Set up replacement tracking' button
    await page.getByRole('link', { name: 'Set up replacement tracking' }).click();

    // expect: The browser navigates to /replacement-tracking
    await expect(page).toHaveURL('/replacement-tracking');
  });

  test("Replacement Tracking panel shows 'All assets within lifespan' when no assets are approaching replacement", async ({ page }) => {
    // 1. Log in as a user with tracking configured but replacement date more than 12 months away
    const uniqueEmail = `repl-within-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add asset with tracking configured well into the future (50 years lifespan, installed in 2020)
    await addAsset(page, 'New Asset');
    await setupReplacementTracking(page, 'New Asset', '2020-01-01', '50');

    // Navigate to dashboard
    await page.goto('/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Replacement Tracking panel
    // expect: The text 'All assets within lifespan.' is displayed
    await expect(page.getByText('All assets within lifespan.')).toBeVisible();
    // expect: No amber-colored count or warning text is visible
    await expect(page.getByText('attention')).not.toBeVisible();
    // expect: A button labelled 'View replacement tracking' is present
    await expect(page.getByRole('link', { name: 'View replacement tracking' })).toBeVisible();

    // 3. Click the 'View replacement tracking' button
    await page.getByRole('link', { name: 'View replacement tracking' }).click();

    // expect: The browser navigates to /replacement-tracking
    await expect(page).toHaveURL('/replacement-tracking');
  });

  test('Replacement Tracking panel shows amber attention count for approaching replacements', async ({ page }) => {
    // 1. Log in as a user with at least one active asset whose replacement date is within 12 months
    // test@example.com has 1 asset needing attention (Refrigerator, replacement date 2019 - overdue)
    await login(page, 'test@example.com');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Replacement Tracking panel
    // expect: A large amber/orange number is displayed
    await expect(page.getByText('1', { exact: true })).toBeVisible();
    // expect: The label reads '[N] asset needs attention (12 months)' or '[N] assets need attention (12 months)'
    await expect(page.getByText(/asset.*attention/)).toBeVisible();
    // expect: A button labelled 'View replacement tracking' is present
    await expect(page.getByRole('link', { name: 'View replacement tracking' })).toBeVisible();

    // 3. Click the 'View replacement tracking' button
    await page.getByRole('link', { name: 'View replacement tracking' }).click();

    // expect: The browser navigates to /replacement-tracking
    await expect(page).toHaveURL('/replacement-tracking');
  });

  test('Replacement Tracking panel uses singular label for exactly one approaching asset', async ({ page }) => {
    // 1. Log in as a user with exactly one asset approaching replacement within 12 months
    // test@example.com has exactly 1 asset needing attention
    await login(page, 'test@example.com');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Replacement Tracking panel count label
    // expect: The count shows '1'
    await expect(page.getByText('1', { exact: true })).toBeVisible();
    // expect: The label reads 'asset needs attention' (singular), not 'assets need attention'
    await expect(page.getByText('asset needs attention (12 months)')).toBeVisible();
    await expect(page.getByText('assets need attention')).not.toBeVisible();
  });

  test('Replacement Tracking panel excludes archived assets from the approaching count', async ({ page }) => {
    // 1. Log in as a user with one archived asset approaching replacement and one active asset safely within lifespan
    const uniqueEmail = `repl-archive-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add two assets
    await addAsset(page, 'Old Asset');
    await addAsset(page, 'New Asset');

    // Setup tracking for Old Asset - approaching replacement (installed 10 years ago, 10 year lifespan = due now)
    await setupReplacementTracking(page, 'Old Asset', '2010-01-01', '10');

    // Archive the Old Asset (triggers a modal confirmation)
    await archiveAsset(page, 'Old Asset');

    // Setup tracking for New Asset - well within lifespan (50 years, installed 2020)
    await setupReplacementTracking(page, 'New Asset', '2020-01-01', '50');

    // Navigate to dashboard
    await page.goto('/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Replacement Tracking panel
    // expect: The archived asset is not counted; only active asset evaluated and it's within lifespan
    await expect(page.getByText('All assets within lifespan.')).toBeVisible();
  });

  test("Replacement Tracking panel only shows current user's data", async ({ page }) => {
    // 1. Log in as User A who has no replacement tracking configured
    // User B (test@example.com) has assets approaching replacement
    const uniqueEmail = `repl-isolation-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed for User A
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the Replacement Tracking panel
    // expect: 'No replacement timelines configured.' is displayed
    await expect(page.getByText('No replacement timelines configured.')).toBeVisible();
    // expect: User B's approaching replacement counts are not visible
    await expect(page.getByText('attention')).not.toBeVisible();
  });
});
