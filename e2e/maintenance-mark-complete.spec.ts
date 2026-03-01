import { test, expect } from './maintenance-fixtures';

// Use a fixed past start date so occurrence due dates are deterministic and never
// coincide with "today". This prevents the assertion from failing on month boundaries
// (e.g. Feb 28) where the next-occurrence date could otherwise equal today's date
// if there were a scheduling bug, making the test pass for the wrong reason.
const FIXED_START_DATE = '2026-01-15'; // first occurrence: Jan 15 → next: Feb 15

test.describe('US3 — Mark Occurrence Complete', () => {
  test('mark complete on /maintenance removes the occurrence and shows the next auto-generated occurrence', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Complete Me Task', startDate: FIXED_START_DATE });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    // After wire:navigate SPA navigation, wait for Livewire to fully initialize
    // the component before interacting — the DOM may render before wire:click
    // handlers are attached, causing clicks to be silently ignored in CI.
    await page.waitForFunction(() =>
      typeof (window as any).Livewire !== 'undefined' &&
      (window as any).Livewire.all().length > 0
    );

    await expect(page.getByText('Complete Me Task')).toBeVisible();

    const markCompleteButton = page.getByRole('button', { name: 'Mark Complete' });
    await expect(markCompleteButton).toBeVisible();
    await expect(markCompleteButton).toBeEnabled();

    // Verify the initial due date is the fixed start date (Jan 15, 2026).
    await expect(page.getByText(/Due: /)).toHaveText(/Due: Jan 15, 2026/);

    await markCompleteButton.click();

    // The old occurrence disappears and a new one appears for the next month (Feb 15, 2026).
    await expect(page.getByText(/Due: /)).toHaveText(/Due: Feb 15, 2026/, { timeout: 10000 });
  });

  test('mark complete on the per-asset view updates the task card with the next due date', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Per Asset Complete Task', startDate: FIXED_START_DATE });

    // Verify the initial due date is the fixed start date (Jan 15, 2026).
    await expect(page.getByText(/Due: /)).toHaveText(/Due: Jan 15, 2026/);

    // Wait for Mark Complete to be enabled before clicking.
    const markCompleteButton = page.getByRole('button', { name: 'Mark Complete' });
    await expect(markCompleteButton).toBeEnabled();
    await markCompleteButton.click();

    // Task card should update with next due date (Livewire reactive update)
    await expect(page.getByText('Per Asset Complete Task').first()).toBeVisible({ timeout: 10000 });

    // Due date should now be one month after the start date (Feb 15, 2026).
    await expect(page.getByText(/Due: /)).toHaveText(/Due: Feb 15, 2026/, { timeout: 10000 });
  });

  test('Mark Complete button triggers the action and the occurrence is replaced with the next one', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'hvac', location: 'Basement' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Loading State Task', startDate: FIXED_START_DATE });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    // After wire:navigate SPA navigation, wait for Livewire to fully initialize
    // the component before interacting — the DOM may render before wire:click
    // handlers are attached, causing clicks to be silently ignored in CI.
    await page.waitForFunction(() =>
      typeof (window as any).Livewire !== 'undefined' &&
      (window as any).Livewire.all().length > 0
    );

    await expect(page.getByText('Loading State Task')).toBeVisible();

    const markCompleteButton = page.getByRole('button', { name: 'Mark Complete' });
    await expect(markCompleteButton).toBeVisible();
    await expect(markCompleteButton).toBeEnabled();

    // Verify the initial due date is the fixed start date (Jan 15, 2026).
    await expect(page.getByText(/Due: /)).toHaveText(/Due: Jan 15, 2026/);

    // The wire:loading "Saving…" span is controlled by Livewire's JS via inline display:none.
    // On localhost requests complete too fast to reliably catch that transient state.
    // Instead, verify the action completes end-to-end: clicking Mark Complete causes
    // the current occurrence to be replaced with the next monthly occurrence.
    await markCompleteButton.click();

    // The next occurrence (one month out, Feb 15, 2026) should now appear, confirming the action ran.
    await expect(page.getByText(/Due: /)).toHaveText(/Due: Feb 15, 2026/, { timeout: 10000 });
    // The Saving… span should not be visible once the request has completed.
    await expect(page.getByText('Saving…')).not.toBeVisible();
  });
});
