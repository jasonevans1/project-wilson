import { test, expect } from './maintenance-fixtures';

test.describe('Task Deactivation', () => {
  test('clicking Deactivate shows a browser native confirm dialog with the correct message', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Dialog Check Task' });

    await expect(page.getByRole('button', { name: 'Deactivate' })).toBeVisible();

    // Set up dialog handler to capture and dismiss
    let dialogMessage = '';
    page.once('dialog', async dialog => {
      dialogMessage = dialog.message();
      await dialog.dismiss();
    });

    await page.getByRole('button', { name: 'Deactivate' }).click();

    // Wait for dialog to be handled
    await page.waitForTimeout(500);

    expect(dialogMessage).toBe('Deactivate this task? Its history will be preserved.');
  });

  test('cancelling the confirm dialog leaves the task visible and unchanged in the list', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Keep Me Task' });

    await expect(page.getByText('Keep Me Task')).toBeVisible();

    // Dismiss the dialog (Cancel)
    page.once('dialog', dialog => dialog.dismiss());
    await page.getByRole('button', { name: 'Deactivate' }).click();

    // Task still visible after dismissal
    await page.waitForTimeout(500);
    await expect(page.getByText('Keep Me Task')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Deactivate' })).toBeVisible();
  });

  test('confirming deactivation removes the task card from the per-asset list without a page reload', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');

    await createMaintenanceTask({ name: 'Remove Me Task' });
    await createMaintenanceTask({ name: 'Preserve This Task' });

    await expect(page.getByText('Remove Me Task')).toBeVisible();
    await expect(page.getByText('Preserve This Task')).toBeVisible();

    // Accept the dialog — "Remove Me Task" was created first, so its Deactivate is the first button
    page.once('dialog', dialog => dialog.accept());

    // Click the first Deactivate button (belongs to "Remove Me Task" — created first, appears first in list)
    await page.getByRole('button', { name: 'Deactivate' }).first().click();

    // Remove Me Task disappears
    await expect(page.getByText('Remove Me Task')).not.toBeVisible({ timeout: 10000 });

    // Preserve This Task remains
    await expect(page.getByText('Preserve This Task')).toBeVisible();
  });

  test('a deactivated task\'s occurrence no longer appears on /maintenance', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Schedule Gone Task' });

    // Capture per-asset maintenance URL before navigating away
    const perAssetMaintenanceUrl = page.url();

    // Verify occurrence is on consolidated schedule
    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');
    await expect(page.getByText('Schedule Gone Task')).toBeVisible();

    // Go back to per-asset page and deactivate
    await page.goto(perAssetMaintenanceUrl);
    await page.waitForURL(/\/assets\/\d+\/maintenance/);
    await expect(page.getByText('Schedule Gone Task')).toBeVisible();

    page.once('dialog', dialog => dialog.accept());
    await page.getByRole('button', { name: 'Deactivate' }).click();
    await expect(page.getByText('Schedule Gone Task')).not.toBeVisible({ timeout: 10000 });

    // Verify it's gone from the consolidated schedule
    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('Schedule Gone Task')).not.toBeVisible();
  });
});
