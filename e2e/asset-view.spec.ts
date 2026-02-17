import { test, expect } from './fixtures';

test.describe('Asset Viewing & Details', () => {
  test('empty state shows helpful message', async ({
    page,
    registerAndGoToAssets,
  }) => {
    await registerAndGoToAssets();

    await expect(page.getByText('No assets yet.')).toBeVisible();
    await expect(
      page.getByText('Get started quickly with pre-built templates or add items manually.'),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Add Asset' }),
    ).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Add Manually' }),
    ).toBeVisible();
    await expect(page.getByLabel('Show Archived')).toBeVisible();
  });

  test('view asset list with active assets', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    // Create 5 assets
    const assets = [
      { name: 'Refrigerator', category: 'appliance', location: 'Kitchen' },
      { name: 'Furnace', category: 'hvac', location: 'Basement' },
      { name: 'Water Heater', category: 'plumbing', location: 'Garage' },
      { name: 'Breaker Panel', category: 'electrical', location: 'Utility' },
      { name: 'Deck', category: 'exterior', location: 'Backyard' },
    ];

    for (const asset of assets) {
      await createAsset(asset);
    }

    // All assets should be visible
    for (const asset of assets) {
      await expect(
        page.getByRole('button', { name: asset.name }),
      ).toBeVisible();
    }
  });

  test('view asset detail with all fields populated', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
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

    await selectAsset('Samsung Refrigerator');

    // Verify detail view content
    await expect(
      page.getByText('Samsung Refrigerator', { exact: true }).first(),
    ).toBeVisible();
    await expect(page.getByText('Appliance')).toBeVisible();
    await expect(page.getByText('Kitchen')).toBeVisible();
    await expect(page.getByText('2024-01-15')).toBeVisible();
    await expect(page.getByText('2024-01-20')).toBeVisible();
    await expect(page.getByText('2027-01-20')).toBeVisible();
    await expect(
      page.getByText(
        'Brand new stainless steel French door refrigerator with ice maker',
      ),
    ).toBeVisible();

    // Action buttons visible
    await expect(page.getByRole('button', { name: 'Back' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Edit' })).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Archive' }),
    ).toBeVisible();
  });

  test('view asset detail with required fields only', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Water Heater',
      category: 'plumbing',
      location: 'Basement',
    });

    await selectAsset('Water Heater');

    await expect(
      page.getByText('Water Heater', { exact: true }).first(),
    ).toBeVisible();
    await expect(page.getByText('Plumbing')).toBeVisible();
    await expect(page.getByText('Basement')).toBeVisible();

    // Optional fields should not be displayed
    await expect(page.getByText('Purchase Date')).toBeHidden();
    await expect(page.getByText('Install Date')).toBeHidden();
    await expect(page.getByText('Warranty Expiration')).toBeHidden();
    await expect(page.getByText('Notes')).toBeHidden();

    // Buttons still visible
    await expect(page.getByRole('button', { name: 'Edit' })).toBeVisible();
    await expect(
      page.getByRole('button', { name: 'Archive' }),
    ).toBeVisible();
  });

  test('navigate back from asset detail', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Test Asset',
      category: 'other',
      location: 'Somewhere',
    });

    await selectAsset('Test Asset');
    await expect(
      page.getByRole('button', { name: 'Back' }),
    ).toBeVisible();

    await page.getByRole('button', { name: 'Back' }).click();

    // Should return to asset list
    await expect(
      page.getByRole('button', { name: 'Test Asset' }),
    ).toBeVisible();
    // Detail heading should be gone
    await expect(
      page.getByRole('button', { name: 'Back' }),
    ).toBeHidden();
  });

  test('view assets from all categories', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    const allCategories = [
      { value: 'appliance', label: 'Appliance', name: 'Fridge', location: 'Kitchen' },
      { value: 'hvac', label: 'HVAC', name: 'Furnace', location: 'Basement' },
      { value: 'plumbing', label: 'Plumbing', name: 'Sink', location: 'Bathroom' },
      { value: 'electrical', label: 'Electrical', name: 'Panel', location: 'Garage' },
      { value: 'roofing', label: 'Roofing', name: 'Shingles', location: 'Roof' },
      { value: 'flooring', label: 'Flooring', name: 'Hardwood', location: 'Living Room' },
      { value: 'exterior', label: 'Exterior', name: 'Fence', location: 'Yard' },
      { value: 'other', label: 'Other', name: 'Misc Item', location: 'Storage' },
    ];

    for (const cat of allCategories) {
      await createAsset({
        name: cat.name,
        category: cat.value,
        location: cat.location,
      });
    }

    // Verify all 8 assets are in the list
    for (const cat of allCategories) {
      await expect(
        page.getByRole('button', { name: cat.name }),
      ).toBeVisible();
    }

    // Click each one and verify the category label
    for (const cat of allCategories) {
      await selectAsset(cat.name);
      await expect(page.getByText(cat.label)).toBeVisible();
      await page.getByRole('button', { name: 'Back' }).click();
    }
  });
});
