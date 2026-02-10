import { test, expect } from './fixtures';

test.describe('Asset Editing', () => {
  test('edit asset - update single field', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Refrigerator',
      category: 'appliance',
      location: 'Kitchen',
    });

    await selectAsset('Refrigerator');
    await page.getByRole('button', { name: 'Edit' }).click();

    // Verify pre-populated values
    await expect(page.getByLabel('Name')).toHaveValue('Refrigerator');
    await expect(page.getByLabel('Category')).toHaveValue('appliance');
    await expect(page.getByLabel('Location')).toHaveValue('Kitchen');

    // Update location
    await page.getByLabel('Location').fill('Main Kitchen');
    await page.getByRole('button', { name: 'Save' }).click();

    // Should return to detail view with updated value
    await expect(page.getByText('Main Kitchen')).toBeVisible();
    // Name unchanged
    await expect(
      page.getByText('Refrigerator', { exact: true }).first(),
    ).toBeVisible();
  });

  test('edit asset - update multiple fields', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Old HVAC',
      category: 'hvac',
      location: 'Basement',
      purchaseDate: '2020-05-10',
    });

    await selectAsset('Old HVAC');
    await page.getByRole('button', { name: 'Edit' }).click();

    await page.getByLabel('Name').fill('New HVAC System');
    await page.getByLabel('Location').fill('Attic');
    await page.getByLabel('Purchase Date').fill('2024-02-01');
    await page.getByLabel('Install Date').fill('2024-02-05');
    await page.getByLabel('Warranty Expiration Date').fill('2029-02-05');
    await page.getByLabel('Notes').fill(
      'High efficiency system installed by certified technician',
    );

    await page.getByRole('button', { name: 'Save' }).click();

    // Verify all updated fields in detail view
    await expect(
      page.getByText('New HVAC System', { exact: true }).first(),
    ).toBeVisible();
    await expect(page.getByText('Attic')).toBeVisible();
    await expect(page.getByText('2024-02-01')).toBeVisible();
    await expect(page.getByText('2024-02-05')).toBeVisible();
    await expect(page.getByText('2029-02-05')).toBeVisible();
    await expect(
      page.getByText(
        'High efficiency system installed by certified technician',
      ),
    ).toBeVisible();
  });

  test('edit asset - change category', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Misc Item',
      category: 'other',
      location: 'Storage',
    });

    await selectAsset('Misc Item');
    await page.getByRole('button', { name: 'Edit' }).click();

    await page.getByLabel('Category').selectOption('electrical');
    await page.getByRole('button', { name: 'Save' }).click();

    // Detail view should show updated category
    await expect(page.getByText('Electrical')).toBeVisible();
  });

  test('edit asset - clear optional fields', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Full Asset',
      category: 'appliance',
      location: 'Kitchen',
      purchaseDate: '2024-01-15',
      installDate: '2024-01-20',
      warrantyExpirationDate: '2027-01-20',
      notes: 'Some notes here',
    });

    await selectAsset('Full Asset');
    await page.getByRole('button', { name: 'Edit' }).click();

    // Clear all optional fields
    await page.getByLabel('Purchase Date').fill('');
    await page.getByLabel('Install Date').fill('');
    await page.getByLabel('Warranty Expiration Date').fill('');
    await page.getByLabel('Notes').fill('');

    await page.getByRole('button', { name: 'Save' }).click();

    // Optional fields should no longer display in detail view
    await expect(page.getByText('Purchase Date')).toBeHidden();
    await expect(page.getByText('Install Date')).toBeHidden();
    await expect(page.getByText('Warranty Expiration')).toBeHidden();
    await expect(page.getByText('Notes')).toBeHidden();
  });

  test('edit asset - validation error on empty name', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Dishwasher',
      category: 'appliance',
      location: 'Kitchen',
    });

    await selectAsset('Dishwasher');
    await page.getByRole('button', { name: 'Edit' }).click();

    // Clear the name field
    await page.getByLabel('Name').fill('');
    await page.getByRole('button', { name: 'Save' }).click();

    // Form should not submit (required field)
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();

    // Fix and save
    await page.getByLabel('Name').fill('Updated Dishwasher');
    await page.getByRole('button', { name: 'Save' }).click();

    await expect(
      page.getByText('Updated Dishwasher', { exact: true }).first(),
    ).toBeVisible();
  });

  test('cancel edit discards changes', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Original Name',
      category: 'other',
      location: 'Original Location',
    });

    await selectAsset('Original Name');
    await page.getByRole('button', { name: 'Edit' }).click();

    // Modify fields
    await page.getByLabel('Name').fill('Modified Name');
    await page.getByLabel('Location').fill('Modified Location');

    // Cancel
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Detail view should show original values
    await expect(
      page.getByText('Original Name', { exact: true }).first(),
    ).toBeVisible();
    await expect(page.getByText('Original Location')).toBeVisible();
  });
});
