// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test, expect } from './replacement-tracking-fixtures';

test.describe('RT3 — Configure Replacement Tracking via Asset Detail Panel', () => {
  test('happy path: configure tracking on an untracked asset via the asset detail panel', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Dishwasher', category: 'appliance', location: 'Kitchen' });

    // 1. Select asset and verify unconfigured state
    await selectAsset('Dishwasher');
    await expect(page.getByText('Replacement tracking not yet configured.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible();

    // 2. Click Set Up and verify form appears with defaults
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toBeVisible();
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('10');
    await expect(page.getByLabel('Installation Date')).toBeVisible();
    await expect(page.getByLabel('Installation Date')).toHaveValue('');
    await expect(page.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();

    // 3. Fill form and save
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('12');
    await page.getByLabel('Installation Date').fill('2019-06-01');
    await page.getByRole('button', { name: 'Save' }).click();

    // Verify tracking info is now displayed
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Expected replacement: 2031')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('years remaining')).toBeVisible();
    await expect(page.locator('.h-2.rounded-full.bg-blue-500')).toBeAttached();
    await expect(page.getByRole('button', { name: 'Edit' }).first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Record Replacement' })).toBeVisible();
    await expect(page.getByText('Replacement tracking not yet configured.')).not.toBeVisible();
  });

  test('category default lifespan is pre-filled in the setup form based on asset category', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();

    // 1. Create HVAC asset, verify default of 15
    await createAsset({ name: 'HVAC Unit', category: 'hvac', location: 'Attic' });
    await selectAsset('HVAC Unit');
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('15');

    // Close the form and go back to list
    await page.getByRole('button', { name: 'Cancel' }).click();
    await page.getByRole('button', { name: 'Back' }).click();
    await expect(page.getByRole('button', { name: 'Add Asset' })).toBeVisible({ timeout: 10000 });

    // 2. Create appliance asset, verify default of 10
    await createAsset({ name: 'Dishwasher', category: 'appliance', location: 'Kitchen' });
    await selectAsset('Dishwasher');
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('10');
  });

  test('existing tracking values are pre-filled when editing via the Edit button on the asset detail panel', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    configureTracking,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Water Heater', category: 'appliance', location: 'Basement' });

    // configureTracking selects the asset and saves, leaving the tracking info visible
    await configureTracking('Water Heater', 8, '2021-03-15');

    // 1. Click Edit (replacement Edit, not asset Edit) and verify pre-filled values
    await page.getByRole('button', { name: 'Edit' }).first().click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' })).toHaveValue('8');
    await expect(page.getByLabel('Installation Date')).toHaveValue('2021-03-15');

    // 2. Change lifespan and save
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('12');
    await page.getByRole('button', { name: 'Save' }).click();

    // Verify form closes and replacement year updated (2021 + 12 = 2033)
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Expected replacement: 2033')).toBeVisible({ timeout: 10000 });
  });

  test('Cancel button closes the setup form without saving changes', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Fan', category: 'other', location: 'Living Room' });
    await selectAsset('Test Fan');

    // 1. Open form and fill some values
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('20');

    // 2. Click Cancel
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Verify form gone, unconfigured state remains
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Replacement tracking not yet configured.')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Set Up' })).toBeVisible();
  });

  test('validation: submitting setup form with empty Installation Date is blocked by browser required constraint', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Test Appliance', category: 'appliance', location: 'Kitchen' });
    await selectAsset('Test Appliance');

    // 1. Open form, leave Installation Date empty, click Save
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // Leave Installation Date empty and click Save
    await page.getByRole('button', { name: 'Save' }).click();

    // Form remains visible — browser required attribute prevents submission
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible();
    await expect(page.getByText('Replacement tracking not yet configured.')).not.toBeVisible();
  });

  test('validation: submitting setup form with a future Installation Date shows an error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Future Asset', category: 'appliance', location: 'Kitchen' });
    await selectAsset('Future Asset');

    // 1. Open form, fill future date, click Save
    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('10');
    await page.getByLabel('Installation Date').fill('2035-01-01');
    await page.getByRole('button', { name: 'Save' }).click();

    // Form stays and shows validation error
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    await expect(page.getByRole('alert').filter({ hasText: 'The install date field must be a date before or equal to today.' })).toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-invalid]')).toBeVisible();
  });

  test('validation: submitting setup form with lifespan out of range shows an error', async ({
    page,
    registerAndGoToAssets,
    createAsset,
    selectAsset,
  }) => {
    await registerAndGoToAssets();
    await createAsset({ name: 'Range Test Asset', category: 'appliance', location: 'Kitchen' });
    await selectAsset('Range Test Asset');

    await page.getByRole('button', { name: 'Set Up' }).click();
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // 1. Set lifespan to 0 (below minimum), fill valid date, click Save
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('0');
    await page.getByLabel('Installation Date').fill('2020-01-01');
    await page.getByRole('button', { name: 'Save' }).click();

    // Browser's min constraint blocks submission — form stays visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });

    // 2. Set lifespan to 101 (above maximum), click Save
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).clear();
    await page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' }).fill('101');
    await page.getByRole('button', { name: 'Save' }).click();

    // Browser's max constraint blocks submission — form stays visible
    await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible({ timeout: 10000 });
  });
});
