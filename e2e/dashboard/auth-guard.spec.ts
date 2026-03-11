// spec: tests/playwright/dashboard/auth-guard.spec.ts
// seed: e2e/seed.spec.ts

import { test, expect } from '@playwright/test';

test.describe('Authentication Guard', () => {
  test('Unauthenticated user is redirected to login page', async ({ page }) => {
    // 1. Open a fresh browser session with no cookies or stored credentials
    // (fresh page context has no auth cookies by default)

    // 2. Navigate directly to /dashboard
    await page.goto('https://project-wilson.ddev.site/dashboard');

    // expect: Redirected to /login
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');
    // expect: Page title reads 'Wilson'
    await expect(page).toHaveTitle('Wilson');
    // expect: Login form is visible with email and password fields
    await expect(page.getByRole('textbox', { name: 'Email address' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Password' })).toBeVisible();
  });

  test('Authenticated user can access the dashboard', async ({ page }) => {
    // 1. Navigate to https://project-wilson.ddev.site/login
    await page.goto('https://project-wilson.ddev.site/login');

    // expect: The login page is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');

    // 2. Enter a valid registered email address in the Email address field
    await page.getByRole('textbox', { name: 'Email address' }).fill('test@example.com');

    // expect: The email field contains the entered value
    await expect(page.getByRole('textbox', { name: 'Email address' })).toHaveValue('test@example.com');

    // 3. Enter the correct password in the Password field
    await page.getByRole('textbox', { name: 'Password' }).fill('password');

    // 4. Click the 'Log in' button
    await page.locator('[data-test="login-button"]').click();

    // expect: Redirected to /dashboard
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');
    // expect: The page heading 'Dashboard' is visible
    await expect(page.getByText('Dashboard').first()).toBeVisible();
    // expect: All four dashboard panels are rendered on the page
    await expect(page.getByText('ASSETS').first()).toBeVisible();
    await expect(page.getByText('MAINTENANCE').first()).toBeVisible();
    await expect(page.getByText('SERVICE RECORDS').first()).toBeVisible();
    await expect(page.getByText('REPLACEMENT TRACKING').first()).toBeVisible();
  });

  test('Login with invalid credentials shows error message', async ({ page }) => {
    // 1. Navigate to https://project-wilson.ddev.site/login
    await page.goto('https://project-wilson.ddev.site/login');

    // expect: The login page is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');

    // 2. Enter 'wrong@example.com' in the Email address field
    await page.getByRole('textbox', { name: 'Email address' }).fill('wrong@example.com');

    // expect: The email field contains the entered value
    await expect(page.getByRole('textbox', { name: 'Email address' })).toHaveValue('wrong@example.com');

    // 3. Enter 'wrongpassword' in the Password field
    await page.getByRole('textbox', { name: 'Password' }).fill('wrongpassword');

    // 4. Click the 'Log in' button
    await page.locator('[data-test="login-button"]').click();

    // expect: The page does not redirect to the dashboard
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');
    // expect: An authentication error message is displayed on the login page
    await expect(page.getByRole('alert')).toContainText('These credentials do not match our records.');
  });

  test('Login form requires email field to be filled', async ({ page }) => {
    // 1. Navigate to https://project-wilson.ddev.site/login
    await page.goto('https://project-wilson.ddev.site/login');

    // expect: The login page is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');

    // 2. Leave the Email address field empty and enter any value in the Password field
    await page.getByRole('textbox', { name: 'Password' }).fill('somepassword');

    // expect: The email field is blank
    await expect(page.getByRole('textbox', { name: 'Email address' })).toHaveValue('');

    // 3. Click the 'Log in' button
    await page.locator('[data-test="login-button"]').click();

    // expect: The form does not submit successfully - remains on login page
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');
  });

  test('Password visibility toggle works on login page', async ({ page }) => {
    // 1. Navigate to https://project-wilson.ddev.site/login
    await page.goto('https://project-wilson.ddev.site/login');

    // expect: The login page is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');

    // 2. Enter 'mypassword' in the Password field
    await page.getByRole('textbox', { name: 'Password' }).fill('mypassword');

    // expect: The password field type is 'password' and characters appear masked
    const passwordInput = page.locator('input[name="password"]');
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // 3. Click the 'Toggle password visibility' button next to the Password field
    await page.getByRole('button', { name: 'Toggle password visibility' }).click();

    // expect: The password field type changes to 'text' and 'mypassword' is readable
    await expect(passwordInput).toHaveAttribute('type', 'text');

    // 4. Click the 'Toggle password visibility' button again
    await page.getByRole('button', { name: 'Toggle password visibility' }).click();

    // expect: The password field type returns to 'password' and characters are masked
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('Forgot password link navigates to password reset page', async ({ page }) => {
    // 1. Navigate to https://project-wilson.ddev.site/login
    await page.goto('https://project-wilson.ddev.site/login');

    // expect: The login page is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');

    // 2. Click the 'Forgot your password?' link
    await page.getByRole('link', { name: 'Forgot your password?' }).click();

    // expect: The browser navigates to https://project-wilson.ddev.site/forgot-password
    await expect(page).toHaveURL('https://project-wilson.ddev.site/forgot-password');
    // expect: A password reset request form is displayed
    await expect(page.getByRole('textbox', { name: 'Email Address' })).toBeVisible();
  });

  test('Sign up link navigates to the registration page', async ({ page }) => {
    // 1. Navigate to https://project-wilson.ddev.site/login
    await page.goto('https://project-wilson.ddev.site/login');

    // expect: The login page is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/login');

    // 2. Click the 'Sign up' link below the login form
    await page.getByRole('link', { name: 'Sign up' }).click();

    // expect: The browser navigates to https://project-wilson.ddev.site/register
    await expect(page).toHaveURL('https://project-wilson.ddev.site/register');
    // expect: A user registration form is displayed
    await expect(page.getByRole('textbox', { name: 'Name' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Email address' })).toBeVisible();
  });
});
