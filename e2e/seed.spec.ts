// Seed file for Project Wilson dashboard tests
// Provides authenticated session setup for test scenarios

import { test, expect } from '@playwright/test';

test.describe('Seed', () => {
  test('seed', async ({ page }) => {
    await page.goto('https://project-wilson.ddev.site/login');
    await page.getByRole('textbox', { name: 'Email address' }).fill('test@example.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('password');
    await page.locator('[data-test="login-button"]').click();
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');
  });
});
