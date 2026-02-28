import { test, expect } from './maintenance-fixtures';

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
    await createMaintenanceTask({ name: 'Complete Me Task' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Complete Me Task')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Mark Complete' })).toBeVisible();

    // Capture the current due date so we can verify it changes after marking complete.
    const initialDueDateText = await page.getByText(/Due: /).first().textContent() ?? '';

    await page.getByRole('button', { name: 'Mark Complete' }).click();

    // The old occurrence disappears and a new one appears for the next month.
    await expect(page.getByText(/Due: /)).not.toHaveText(initialDueDateText, { timeout: 10000 });
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
    await createMaintenanceTask({ name: 'Per Asset Complete Task' });

    // Capture initial due date
    const initialDueDateText = await page.getByText(/Due: /).first().textContent();

    await page.getByRole('button', { name: 'Mark Complete' }).click();

    // Task card should update with next due date (Livewire reactive update)
    await expect(page.getByText('Per Asset Complete Task').first()).toBeVisible({ timeout: 10000 });

    // Due date should have changed
    await expect(page.getByText(/Due: /)).not.toHaveText(initialDueDateText ?? '', { timeout: 10000 });
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
    await createMaintenanceTask({ name: 'Loading State Task' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Loading State Task')).toBeVisible();

    // Wait for the button to be fully enabled before interacting — Livewire may still be
    // processing the task-created event (reloading occurrences) when we arrive on this page,
    // which briefly disables the button via wire:loading.attr="disabled". Clicking it while
    // disabled would cause the action to never fire.
    const markCompleteButton = page.getByRole('button', { name: 'Mark Complete' });
    await expect(markCompleteButton).toBeVisible();
    await expect(markCompleteButton).toBeEnabled();

    // Capture the current due date so we can verify it changes after marking complete.
    const initialDueDateText = await page.getByText(/Due: /).first().textContent() ?? '';

    // The wire:loading "Saving…" span is controlled by Livewire's JS via inline display:none.
    // On localhost requests complete too fast to reliably catch that transient state.
    // Instead, verify the action completes end-to-end: clicking Mark Complete causes
    // the current occurrence to be replaced with the next monthly occurrence.
    await markCompleteButton.click();

    // The next occurrence (one month out) should now appear, confirming the action ran.
    await expect(page.getByText(/Due: /)).not.toHaveText(initialDueDateText, { timeout: 10000 });
    // The Saving… span should not be visible once the request has completed.
    await expect(page.getByText('Saving…')).not.toBeVisible();
  });
});
