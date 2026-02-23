import { test, expect } from './maintenance-fixtures';

test.describe('US4 — Edit Occurrence Due Date (Inline)', () => {
  test('pencil icon opens the inline date editor pre-filled with the current due date', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Pencil Edit Task', startDate: '2027-06-15' });

    await expect(page.getByText('Due: Jun 15, 2027')).toBeVisible();

    // Click pencil icon (button with wire:click starting with "startEditDueDate")
    await page.locator('[wire\\:click^="startEditDueDate"]').click();

    // Date input appears pre-filled
    const dateInput = page.locator('input[name="editDueDate"]');
    await expect(dateInput).toBeVisible();
    await expect(dateInput).toHaveValue('2027-06-15');

    // Save and Cancel buttons visible
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();

    // Pencil icon no longer visible
    await expect(page.locator('[wire\\:click^="startEditDueDate"]')).not.toBeVisible();
  });

  test('cancel closes the inline editor and restores the original due date without saving changes', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Cancel Edit Task', startDate: '2027-08-20' });

    await expect(page.getByText('Due: Aug 20, 2027')).toBeVisible();

    // Open editor
    await page.locator('[wire\\:click^="startEditDueDate"]').click();

    // Change the date
    await page.locator('input[name="editDueDate"]').fill('2029-01-01');

    // Cancel
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Editor gone, original date restored
    await expect(page.locator('input[name="editDueDate"]')).not.toBeVisible();
    await expect(page.getByText('Due: Aug 20, 2027')).toBeVisible();
    await expect(page.locator('[wire\\:click^="startEditDueDate"]')).toBeVisible();
  });

  test('save persists the new due date and returns the card to view mode', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'Date Update Task', startDate: '2027-05-10' });

    await expect(page.getByText('Due: May 10, 2027')).toBeVisible();

    // Open editor
    await page.locator('[wire\\:click^="startEditDueDate"]').click();

    // Change the date
    await page.locator('input[name="editDueDate"]').fill('2028-11-30');

    // Save
    await page.getByRole('button', { name: 'Save' }).click();

    // Editor gone, new date shown
    await expect(page.locator('input[name="editDueDate"]')).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Due: Nov 30, 2028')).toBeVisible();
    await expect(page.locator('[wire\\:click^="startEditDueDate"]')).toBeVisible();
  });

  test('opening the inline editor on a second card closes the editor on the first card', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');

    await createMaintenanceTask({ name: 'Edit Task One', startDate: '2027-04-01' });
    await createMaintenanceTask({ name: 'Edit Task Two', startDate: '2027-07-01' });

    await expect(page.getByText('Edit Task One')).toBeVisible();
    await expect(page.getByText('Edit Task Two')).toBeVisible();

    const pencilButtons = page.locator('[wire\\:click^="startEditDueDate"]');

    // Open editor on first task
    await pencilButtons.first().click();

    const dateInputs = page.locator('input[name="editDueDate"]');
    await expect(dateInputs).toHaveCount(1);

    // Open editor on second task — this should close the first
    await pencilButtons.last().click();

    // Still only one date input visible (the second one)
    await expect(dateInputs).toHaveCount(1);
    await expect(dateInputs.first()).toHaveValue('2027-07-01');
  });

  test('no pencil icon or inline editor is available on the /maintenance consolidated schedule page', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    goToAssetMaintenance,
    createMaintenanceTask,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Unit', category: 'other', location: 'Garage' });
    await goToAssetMaintenance('Test Unit');
    await createMaintenanceTask({ name: 'No Pencil Task' });

    await page.getByRole('link', { name: 'Maintenance' }).click();
    await page.waitForURL('**/maintenance');

    await expect(page.getByText('No Pencil Task')).toBeVisible();

    // No pencil icon buttons on the schedule page
    await expect(page.locator('[wire\\:click^="startEditDueDate"]')).toHaveCount(0);
    await expect(page.locator('input[name="editDueDate"]')).toHaveCount(0);
  });
});
