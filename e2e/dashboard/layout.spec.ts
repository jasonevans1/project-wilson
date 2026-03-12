// spec: tests/playwright/dashboard/layout.spec.ts
// seed: e2e/seed.spec.ts

import { test, expect } from '@playwright/test';

async function login(page: any) {
  await page.goto('/login');
  await page.getByRole('textbox', { name: 'Email address' }).fill('test@example.com');
  await page.getByRole('textbox', { name: 'Password' }).fill('password');
  await page.locator('[data-test="login-button"]').click();
  await expect(page).toHaveURL('/dashboard');
}

test.describe('Dashboard Layout and Navigation', () => {
  test('Dashboard page renders heading and four panels', async ({ page }) => {
    // 1. Log in with valid credentials and navigate to /dashboard
    await login(page);

    // expect: The user is on the dashboard page
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the main content area of the page
    // expect: A 'Dashboard' heading is visible
    await expect(page.getByText('Dashboard').first()).toBeVisible();
    // expect: The four panels are labelled: 'ASSETS', 'MAINTENANCE', 'SERVICE RECORDS', and 'REPLACEMENT TRACKING'
    await expect(page.getByText('ASSETS').first()).toBeVisible();
    await expect(page.getByText('MAINTENANCE').first()).toBeVisible();
    await expect(page.getByText('SERVICE RECORDS').first()).toBeVisible();
    await expect(page.getByText('REPLACEMENT TRACKING').first()).toBeVisible();
  });

  test('Sidebar shows all navigation items with correct active state on dashboard', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The dashboard page is displayed with the sidebar visible
    await expect(page).toHaveURL('/dashboard');

    // 2. Inspect the sidebar navigation
    // expect: The Wilson logo link is present in the sidebar header
    await expect(page.getByRole('link', { name: 'Wilson' })).toBeVisible();

    // expect: Under a 'Platform' group: navigation items are visible
    await expect(page.getByText('Platform')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Assets', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Maintenance' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Replacement Tracking', exact: true })).toBeVisible();

    // expect: The 'Dashboard' item is highlighted as the current/active page
    await expect(page.locator('a[data-flux-sidebar-item][href$="/dashboard"][data-current]')).toBeVisible();
  });

  test('Sidebar Assets link navigates to assets page', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The sidebar is visible with the Dashboard item active
    await expect(page.locator('a[data-flux-sidebar-item][href$="/dashboard"][data-current]')).toBeVisible();

    // 2. Click the 'Assets' link in the sidebar
    await page.getByRole('link', { name: 'Assets', exact: true }).click();

    // expect: The browser navigates to /assets
    await expect(page).toHaveURL('/assets');
    // expect: The 'Assets' sidebar item is now highlighted as active
    await expect(page.locator('a[href$="/assets"][data-current]')).toBeVisible();
  });

  test('Sidebar Maintenance link navigates to maintenance schedule page', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The sidebar is visible
    await expect(page).toHaveURL('/dashboard');

    // 2. Click the 'Maintenance' link in the sidebar
    await page.getByRole('link', { name: 'Maintenance' }).click();

    // expect: The browser navigates to /maintenance
    await expect(page).toHaveURL('/maintenance');
    // expect: The 'Maintenance' sidebar item is now highlighted as active
    await expect(page.locator('a[href$="/maintenance"][data-current]')).toBeVisible();
  });

  test('Sidebar Replacement Tracking link navigates to replacement tracking page', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The sidebar is visible
    await expect(page).toHaveURL('/dashboard');

    // 2. Click the 'Replacement Tracking' link in the sidebar
    await page.getByRole('link', { name: 'Replacement Tracking', exact: true }).click();

    // expect: The browser navigates to /replacement-tracking
    await expect(page).toHaveURL('/replacement-tracking');
    // expect: The 'Replacement Tracking' sidebar item is now highlighted as active
    await expect(page.locator('a[href$="/replacement-tracking"][data-current]')).toBeVisible();
  });

  test('Wilson logo in sidebar navigates back to dashboard', async ({ page }) => {
    // 1. Log in and navigate to /assets
    await login(page);
    await page.goto('/assets');

    // expect: The assets page is displayed
    await expect(page).toHaveURL('/assets');

    // 2. Click the 'Wilson' logo in the sidebar header
    await page.getByRole('link', { name: 'Wilson' }).click();

    // expect: The browser navigates to /dashboard
    await expect(page).toHaveURL('/dashboard');
    // expect: The dashboard with all four panels is displayed
    await expect(page.getByText('ASSETS').first()).toBeVisible();
    await expect(page.getByText('MAINTENANCE').first()).toBeVisible();
    await expect(page.getByText('SERVICE RECORDS').first()).toBeVisible();
    await expect(page.getByText('REPLACEMENT TRACKING').first()).toBeVisible();
  });

  test('User profile menu opens and displays user identity', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Click the user profile button at the bottom of the sidebar
    await page.locator('[data-test="sidebar-menu-button"]').click();

    // expect: A dropdown menu expands
    await expect(page.getByRole('menu')).toBeVisible();
    // expect: The menu header shows the user's full name and email address
    await expect(page.getByRole('menu').getByText('Test User')).toBeVisible();
    await expect(page.getByRole('menu').getByText('test@example.com')).toBeVisible();
    // expect: Two menu items are listed: 'Settings' and 'Log Out'
    await expect(page.getByRole('menuitem', { name: 'Settings' })).toBeVisible();
    await expect(page.getByRole('menuitem', { name: 'Log Out' })).toBeVisible();
  });

  test('Settings menu item navigates to the profile settings page', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Click the user profile button to open the dropdown menu
    await page.locator('[data-test="sidebar-menu-button"]').click();

    // expect: The dropdown is open with 'Settings' and 'Log Out' visible
    await expect(page.getByRole('menuitem', { name: 'Settings' })).toBeVisible();
    await expect(page.getByRole('menuitem', { name: 'Log Out' })).toBeVisible();

    // 3. Click the 'Settings' menu item
    await page.getByRole('menuitem', { name: 'Settings' }).click();

    // expect: The browser navigates to the profile/settings page
    await expect(page).toHaveURL('/settings/profile');
    // expect: A settings or profile edit form is displayed
    await expect(page.getByRole('textbox', { name: 'Name' })).toBeVisible();
  });

  test('Log Out menu item ends the session and redirects to login', async ({ page }) => {
    // 1. Log in and navigate to the dashboard
    await login(page);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('/dashboard');

    // 2. Click the user profile button to open the dropdown menu, then click 'Log Out'
    await page.locator('[data-test="sidebar-menu-button"]').click();
    await page.getByRole('menuitem', { name: 'Log Out' }).click();

    // expect: The user's session is terminated and redirected away from dashboard
    await expect(page).not.toHaveURL('/dashboard');

    // 3. Navigate directly to /dashboard
    await page.goto('/dashboard');

    // expect: The browser redirects to /login because the session has ended
    await expect(page).toHaveURL('/login');
  });
});
