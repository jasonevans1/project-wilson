// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT5 — Record Asset Replacement via Asset Detail Panel', () => {
  test('happy path: record replacement with required field only', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    recordReplacement,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Refrigerator', category: 'appliance', location: 'Kitchen' });

    // configureTracking selects the asset and saves, leaving tracking info visible
    await configureTracking('Refrigerator', 15, '2015-01-01');

    // Click 'Record Replacement' button to open the form
    await page.getByRole('button', { name: 'Record Replacement' }).click();

    // Verify form fields are visible with correct defaults
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByLabel('New Installation Date')).toBeVisible();
    await expect(page.getByLabel('New Installation Date')).toHaveValue('');
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toBeVisible();
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('15');
    await expect(page.getByLabel('Replacement Cost (optional)')).toBeVisible();
    await expect(page.getByLabel('Replacement Cost (optional)')).toHaveValue('');
    await expect(page.getByLabel('Notes (optional)')).toBeVisible();
    await expect(page.getByLabel('Notes (optional)')).toHaveValue('');
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();

    // Fill required field and save
    await recordReplacement('2026-02-01');

    // Verify form closed and replacement data updated
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Expected replacement: 2041')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Replacement History')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('2026-02-01').first()).toBeVisible({ timeout: 10000 });
  });

  test('happy path: record replacement with all optional fields', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Washing Machine', category: 'appliance', location: 'Kitchen' });
    await configureTracking('Washing Machine', 10, '2015-01-01');

    // Click 'Record Replacement' button to open the form
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Fill New Installation Date (must be in the past)
    await page.getByLabel('New Installation Date').fill('2026-02-15');

    // Clear and fill Expected Lifespan
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('12');

    // Fill Replacement Cost
    await page.getByLabel('Replacement Cost (optional)').fill('750.00');

    // Fill Notes
    await page.getByLabel('Notes (optional)').fill('Replaced old failing unit');

    // Click Save
    await page.getByRole('button', { name: 'Save' }).click();

    // Verify form closed and all data is shown
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Expected replacement: 2038').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Replacement History')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('2026-02-15').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('$750.00')).toBeVisible({ timeout: 10000 });
  });

  test('multiple replacements accumulate in history', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    recordReplacement,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Dryer', category: 'appliance', location: 'Basement' });
    await configureTracking('Dryer', 10, '2010-01-01');

    // Record first replacement
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await recordReplacement('2022-01-01', { cost: '300' });

    // Verify first replacement
    await expect(page.getByText('2022-01-01').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('$300.00')).toBeVisible({ timeout: 10000 });

    // Record second replacement
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await recordReplacement('2026-01-10', { cost: '450' });

    // Verify second replacement is visible
    await expect(page.getByText('2026-01-10').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('$450.00')).toBeVisible({ timeout: 10000 });

    // Verify first replacement is still visible
    await expect(page.getByText('2022-01-01').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('$300.00')).toBeVisible({ timeout: 10000 });
  });

  test('Cancel closes form without saving', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Dishwasher', category: 'appliance', location: 'Kitchen' });
    await configureTracking('Dishwasher', 10, '2018-06-01');

    // Click 'Record Replacement' button to open the form
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Fill some values
    await page.getByLabel('New Installation Date').fill('2026-01-01');
    await page.getByLabel('Replacement Cost (optional)').fill('500');

    // Click Cancel
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Verify form closed and no history was recorded
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Replacement History')).not.toBeVisible();

    // Verify tracking info is still showing (not altered by cancelled save)
    await expect(page.getByText(/years remaining|years overdue/)).toBeVisible({ timeout: 10000 });
  });

  test('recording replacement updates overdue asset to active', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
    recordReplacement,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Old Water Heater', category: 'appliance', location: 'Basement' });

    // Configure with a lifespan that makes the asset overdue
    await configureTracking('Old Water Heater', 10, '2010-01-01');

    // Verify asset is overdue
    await expect(page.getByText(/years overdue/)).toBeVisible({ timeout: 10000 });

    // Record a replacement with a recent date
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await recordReplacement('2026-03-01');

    // Verify form closed and asset is no longer overdue
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText(/years remaining/)).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('2036')).toBeVisible({ timeout: 10000 });
  });

  test('validation: empty New Installation Date blocked by browser required', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Oven', category: 'appliance', location: 'Kitchen' });
    await configureTracking('Oven', 10, '2020-01-01');

    // Click 'Record Replacement' button to open the form
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Leave date empty and click Save — browser required attribute prevents submission
    await page.getByRole('button', { name: 'Save' }).click();

    // Form should remain visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible();
  });

  test('validation: future date shows server error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Microwave', category: 'appliance', location: 'Kitchen' });
    await configureTracking('Microwave', 10, '2020-01-01');

    // Click 'Record Replacement' button to open the form
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Fill a future date and click Save
    await page.getByLabel('New Installation Date').fill('2035-01-01');
    await page.getByRole('button', { name: 'Save' }).click();

    // Verify form stays and error message is shown
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });
    await expect(
      page.getByRole('alert').filter({ hasText: 'The installed at field must be a date before or equal to today.' }),
    ).toBeVisible({ timeout: 10000 });
  });

  test('validation: negative cost shows error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Freezer', category: 'appliance', location: 'Basement' });
    await configureTracking('Freezer', 10, '2020-01-01');

    // Click 'Record Replacement' button to open the form
    await page.getByRole('button', { name: 'Record Replacement' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });

    // Fill valid date and negative cost
    await page.getByLabel('New Installation Date').fill('2020-01-01');
    await page.getByLabel('Replacement Cost (optional)').fill('-100');
    await page.getByRole('button', { name: 'Save' }).click();

    // Browser's min="0" constraint blocks submission — form stays visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible({ timeout: 10000 });
  });
});
