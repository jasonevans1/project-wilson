import { test, expect } from './fixtures';

test.describe('Edge Cases & Boundary Conditions', () => {
  test('very long asset name (255 characters)', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    const longName = 'A'.repeat(255);

    await createAsset({
      name: longName,
      category: 'appliance',
      location: 'Kitchen',
    });

    // Asset should be created and visible in list
    await expect(page.getByText(longName)).toBeVisible();

    // View detail
    await page.getByText(longName).click();
    await expect(page.getByText(longName).first()).toBeVisible();
  });

  test('very long notes text (2000 characters)', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    const longNotes = 'N'.repeat(2000);

    await createAsset({
      name: 'Long Notes Asset',
      category: 'other',
      location: 'Storage',
      notes: longNotes,
    });

    await selectAsset('Long Notes Asset');
    await expect(page.getByText(longNotes)).toBeVisible();
  });

  test('special characters in asset fields', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Fridge & Freezer <Model #123>',
      category: 'appliance',
      location: 'Kitchen (Main) / Pantry Area',
      notes: 'Contains "special" chars: <script>alert("xss")</script> & more',
    });

    await selectAsset('Fridge & Freezer <Model #123>');

    // Verify special characters are properly escaped and displayed
    await expect(
      page.getByText('Fridge & Freezer <Model #123>', { exact: true }).first(),
    ).toBeVisible();
    await expect(
      page.getByText('Kitchen (Main) / Pantry Area'),
    ).toBeVisible();
    await expect(
      page.getByText(
        'Contains "special" chars: <script>alert("xss")</script> & more',
      ),
    ).toBeVisible();
  });

  test('date field edge cases', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    // Future purchase date
    await createAsset({
      name: 'Future Purchase',
      category: 'appliance',
      location: 'Kitchen',
      purchaseDate: '2030-12-31',
    });
    await selectAsset('Future Purchase');
    await expect(page.getByText('2030-12-31')).toBeVisible();
    await page.getByRole('button', { name: 'Back' }).click();

    // Install date before purchase date
    await createAsset({
      name: 'Backwards Dates',
      category: 'hvac',
      location: 'Basement',
      purchaseDate: '2024-06-01',
      installDate: '2024-01-01',
    });
    await selectAsset('Backwards Dates');
    await expect(page.getByText('2024-06-01')).toBeVisible();
    await expect(page.getByText('2024-01-01')).toBeVisible();
    await page.getByRole('button', { name: 'Back' }).click();

    // Very old date
    await createAsset({
      name: 'Vintage Item',
      category: 'other',
      location: 'Attic',
      purchaseDate: '1950-01-01',
    });
    await selectAsset('Vintage Item');
    await expect(page.getByText('1950-01-01')).toBeVisible();
  });

  test('rapid action sequence', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Rapid Asset',
      category: 'other',
      location: 'Room',
    });

    // Quick: select -> edit -> modify -> save
    await selectAsset('Rapid Asset');
    await page.getByRole('button', { name: 'Edit' }).click();
    await page.getByLabel('Name').fill('Rapidly Edited');
    await page.getByRole('button', { name: 'Save' }).click();

    await expect(
      page.getByText('Rapidly Edited', { exact: true }).first(),
    ).toBeVisible();

    // Quick: archive flow
    await page.getByRole('button', { name: 'Archive' }).first().click();
    await page
      .getByRole('dialog')
      .getByRole('button', { name: 'Archive' })
      .click();

    // Should be back to empty state
    await expect(page.getByText('No assets yet.')).toBeVisible();
  });

  test('browser back button navigation', async ({
    page,
    registerAndGoToAssets,
    createAsset,
  }) => {
    await registerAndGoToAssets();

    await createAsset({
      name: 'Nav Test Asset',
      category: 'other',
      location: 'Room',
    });

    // Navigate to dashboard via sidebar link
    await page.getByRole('link', { name: 'Dashboard' }).click();
    await page.waitForURL('**/dashboard');

    // Go back
    await page.goBack();
    await page.waitForURL('**/assets');

    // Assets page should still work
    await expect(page.getByText('Assets', { exact: true }).first()).toBeVisible();
  });

  test('empty vs null for optional fields', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    // Create with whitespace-only notes
    await createAsset({
      name: 'Whitespace Notes',
      category: 'other',
      location: 'Room',
      notes: '   ',
    });

    await selectAsset('Whitespace Notes');

    // Edit and clear notes completely
    await page.getByRole('button', { name: 'Edit' }).click();
    await page.getByLabel('Notes').fill('');
    await page.getByRole('button', { name: 'Save' }).click();

    // Wait for edit form to close (Edit button becomes visible again)
    await expect(page.getByRole('button', { name: 'Edit' })).toBeVisible();

    // Notes section should not display (check for the uppercase label text that appears above notes content)
    await expect(
      page.getByText('Notes', { exact: true }),
    ).toBeHidden();
  });
});
