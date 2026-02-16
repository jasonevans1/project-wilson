import { test, expect } from './fixtures';

test.describe('Asset Creation - Happy Path', () => {
  test('create asset with all fields', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Samsung Refrigerator',
      category: 'appliance',
      location: 'Kitchen',
      purchaseDate: '2024-01-15',
      installDate: '2024-01-20',
      warrantyExpirationDate: '2027-01-20',
      notes:
        'Brand new stainless steel French door refrigerator with ice maker',
    });

    await expect(page.getByText('Asset added.')).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Samsung Refrigerator' }),
    ).toBeVisible();
    await expect(page.getByText('Appliance · Kitchen')).toBeVisible();
  });

  test('create asset with required fields only', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'HVAC Unit',
      category: 'hvac',
      location: 'Basement',
    });

    await expect(page.getByText('Asset added.')).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'HVAC Unit' }),
    ).toBeVisible();
    await expect(page.getByText('HVAC · Basement')).toBeVisible();
  });

  test('create multiple assets with different categories', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Water Heater',
      category: 'plumbing',
      location: 'Garage',
    });
    await page.waitForTimeout(1100);
    await createAsset({
      name: 'Circuit Breaker Panel',
      category: 'electrical',
      location: 'Utility Room',
    });
    await page.waitForTimeout(1100);
    await createAsset({
      name: 'Asphalt Shingles',
      category: 'roofing',
      location: 'Exterior',
    });
    await page.waitForTimeout(1100);
    await createAsset({
      name: 'Hardwood Floors',
      category: 'flooring',
      location: 'Living Room',
    });

    // All 4 assets should be visible in the list
    await expect(
      page.getByRole('button', { name: 'Water Heater' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Circuit Breaker Panel' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Asphalt Shingles' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Hardwood Floors' }),
    ).toBeVisible();

    // Latest asset should appear first (reverse chronological)
    const assetButtons = page.locator(
      'button[wire\\:click^="selectAsset"]',
    );
    const firstAssetButton = assetButtons.first();
    await expect(firstAssetButton).toContainText('Hardwood Floors');
  });
});

test.describe('Asset Creation - Validation & Error Handling', () => {
  test('validation error when name is missing', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await page.getByRole('button', { name: 'Add Asset' }).click();
    // Leave name empty
    await page.getByLabel('Category').selectOption('appliance');
    await page.getByLabel('Location').fill('Kitchen');
    await page.getByRole('button', { name: 'Save' }).click();

    // Form should not submit - the Name input has `required` attribute
    // We should still be on the form
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
  });

  test('validation error when category is missing', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await page.getByRole('button', { name: 'Add Asset' }).click();
    await page.getByLabel('Name').fill('Dishwasher');
    // Leave category at default disabled option
    await page.getByLabel('Location').fill('Kitchen');
    await page.getByRole('button', { name: 'Save' }).click();

    // Form should not submit due to required validation
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
  });

  test('validation error when location is missing', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await page.getByRole('button', { name: 'Add Asset' }).click();
    await page.getByLabel('Name').fill('Microwave');
    await page.getByLabel('Category').selectOption('appliance');
    // Leave location empty
    await page.getByRole('button', { name: 'Save' }).click();

    // Form should not submit
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
  });

  test('validation error when all required fields are missing', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await page.getByRole('button', { name: 'Add Asset' }).click();
    // Leave all fields empty
    await page.getByRole('button', { name: 'Save' }).click();

    // Form should not submit
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
  });

  test('cancel asset creation discards form data', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await page.getByRole('button', { name: 'Add Asset' }).click();
    await page.getByLabel('Name').fill('Test Asset');
    await page.getByLabel('Category').selectOption('other');
    await page.getByLabel('Location').fill('Test Location');

    await page.getByRole('button', { name: 'Cancel' }).click();

    // Should return to asset list (empty state)
    await expect(page.getByText('No assets yet.')).toBeVisible();
    // Form should be gone
    await expect(page.getByLabel('Name')).toBeHidden();
  });
});
