// spec: tests/playwright/dashboard/maintenance-panel.spec.ts
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

async function addAsset(page: any, name = 'Test Asset') {
  await page.goto('https://project-wilson.ddev.site/assets');
  await page.getByRole('button', { name: 'Add Asset' }).click();
  await page.getByRole('textbox', { name: 'Name' }).fill(name);
  await page.getByRole('combobox', { name: 'Category' }).selectOption('Other');
  await page.getByRole('textbox', { name: 'Location' }).fill('Home');
  await page.getByRole('button', { name: 'Save' }).click();
  await expect(page.getByRole('button', { name: new RegExp(name) })).toBeVisible();
}

async function addMaintenanceTask(page: any, assetName: string, taskName: string, startDate: string) {
  await page.goto('https://project-wilson.ddev.site/assets');
  await page.getByRole('button', { name: new RegExp(assetName) }).click();
  await page.getByRole('link', { name: 'View Maintenance' }).click();
  await page.getByRole('textbox', { name: 'Task Name' }).fill(taskName);
  await page.getByLabel('Start Date').fill(startDate);
  await page.getByRole('button', { name: 'Add Task' }).click();
  await expect(page.getByText(taskName)).toBeVisible();
}

test.describe('Maintenance Panel', () => {
  test('Maintenance panel shows empty state when user has no tasks', async ({ page }) => {
    // 1. Log in as a user with no maintenance tasks and navigate to the dashboard
    const uniqueEmail = `maint-empty-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Maintenance panel
    // expect: The text 'No maintenance tasks scheduled yet.' is displayed
    await expect(page.getByText('No maintenance tasks scheduled yet.')).toBeVisible();
    // expect: No overdue or upcoming badges are visible
    await expect(page.getByText('overdue')).not.toBeVisible();
    await expect(page.getByText('upcoming')).not.toBeVisible();
    // expect: A button labelled 'Create your first task' is present
    await expect(page.getByRole('link', { name: 'Create your first task' })).toBeVisible();

    // 3. Click the 'Create your first task' button
    await page.getByRole('link', { name: 'Create your first task' }).click();

    // expect: The browser navigates to https://project-wilson.ddev.site/maintenance
    await expect(page).toHaveURL('https://project-wilson.ddev.site/maintenance');
  });

  test("Maintenance panel shows 'All caught up!' when tasks exist but nothing is overdue or upcoming", async ({ page }) => {
    // 1. Log in as a user with tasks but all occurrences more than 7 days away
    // test@example.com has a Servicing task for HVAC Unit due Jul 15, 2026 (well more than 7 days away from today Mar 9, 2026)
    // and an overdue task. We need a user where no tasks are overdue and none within 7 days.
    const uniqueEmail = `maint-caughtup-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add an asset and a task with a far-future start date (more than 7 days away)
    await addAsset(page, 'Test Asset');
    await addMaintenanceTask(page, 'Test Asset', 'Future Task', '2030-01-01');

    // Navigate to dashboard
    await page.goto('https://project-wilson.ddev.site/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Maintenance panel
    // expect: The text 'All caught up!' is displayed
    await expect(page.getByText('All caught up!')).toBeVisible();
    // expect: No overdue or upcoming badge counts are visible
    await expect(page.getByText('overdue')).not.toBeVisible();
    await expect(page.getByText('upcoming')).not.toBeVisible();
    // expect: A button labelled 'View schedule' is present
    await expect(page.getByRole('link', { name: 'View schedule' })).toBeVisible();
  });

  test('Maintenance panel shows red overdue badge when overdue occurrences exist', async ({ page }) => {
    // 1. Log in as a user with at least one incomplete maintenance occurrence past its due date
    // test@example.com has 1 overdue task (Cleaning for Dishwasher, due Mar 5, 2026)
    await login(page, 'test@example.com');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Maintenance panel
    // expect: A red badge is displayed reading '[N] overdue'
    await expect(page.getByText(/\d+ overdue/)).toBeVisible();
    // expect: A button labelled 'View schedule' is present
    await expect(page.getByRole('link', { name: 'View schedule' })).toBeVisible();

    // 3. Click the 'View schedule' button
    await page.getByRole('link', { name: 'View schedule' }).click();

    // expect: The browser navigates to https://project-wilson.ddev.site/maintenance
    await expect(page).toHaveURL('https://project-wilson.ddev.site/maintenance');
  });

  test('Maintenance panel shows zinc upcoming badge for tasks due within 7 days', async ({ page }) => {
    // 1. Log in as a user with at least one incomplete occurrence due within 7 days
    const uniqueEmail = `maint-upcoming-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add an asset and a task starting today (within 7 days)
    await addAsset(page, 'Test Asset');
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    await addMaintenanceTask(page, 'Test Asset', 'Soon Task', todayStr);

    // Navigate to dashboard
    await page.goto('https://project-wilson.ddev.site/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Maintenance panel
    // expect: A neutral (zinc) badge is displayed reading '[N] upcoming'
    await expect(page.getByText(/\d+ upcoming/)).toBeVisible();
    // expect: A button labelled 'View schedule' is present
    await expect(page.getByRole('link', { name: 'View schedule' })).toBeVisible();
  });

  test('Maintenance panel shows both overdue and upcoming badges simultaneously when both exist', async ({ page }) => {
    // 1. Log in as a user who has both overdue and upcoming occurrences
    const uniqueEmail = `maint-both-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // Add an asset with two tasks: one overdue, one upcoming
    await addAsset(page, 'Test Asset');
    // Add an overdue task (start date in the past)
    await addMaintenanceTask(page, 'Test Asset', 'Overdue Task', '2024-01-01');
    // Add an upcoming task (start date today)
    await page.goto('https://project-wilson.ddev.site/assets');
    await page.getByRole('button', { name: /Test Asset/ }).click();
    await page.getByRole('link', { name: 'View Maintenance' }).click();
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    await page.getByRole('textbox', { name: 'Task Name' }).fill('Upcoming Task');
    await page.getByLabel('Start Date').fill(todayStr);
    await page.getByRole('button', { name: 'Add Task' }).click();
    await expect(page.getByText('Upcoming Task')).toBeVisible();

    // Navigate to dashboard
    await page.goto('https://project-wilson.ddev.site/dashboard');

    // expect: The dashboard is displayed
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Maintenance panel
    // expect: Both the red overdue badge and the zinc upcoming badge are visible simultaneously
    await expect(page.getByText(/\d+ overdue/)).toBeVisible();
    await expect(page.getByText(/\d+ upcoming/)).toBeVisible();
    // expect: A 'View schedule' button is present
    await expect(page.getByRole('link', { name: 'View schedule' })).toBeVisible();
  });

  test("Maintenance panel does not display another user's task data", async ({ page }) => {
    // 1. Log in as User A who has no maintenance tasks
    // Ensure User B (test@example.com) has overdue tasks
    const uniqueEmail = `maint-isolation-${Date.now()}@example.com`;
    await registerNewUser(page, uniqueEmail);

    // expect: The dashboard is displayed for User A
    await expect(page).toHaveURL('https://project-wilson.ddev.site/dashboard');

    // 2. Inspect the Maintenance panel
    // expect: 'No maintenance tasks scheduled yet.' is displayed
    await expect(page.getByText('No maintenance tasks scheduled yet.')).toBeVisible();
    // expect: No overdue badge referencing User B's data appears
    await expect(page.getByText('overdue')).not.toBeVisible();
  });
});
