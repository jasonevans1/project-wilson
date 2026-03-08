// spec: e2e/replacement-tracking.plan.md
// seed: e2e/seed.spec.ts

import { test as base, expect } from './fixtures';

export interface ReplacementTrackingFixtures {
  /** Navigate to /replacement-tracking via the sidebar Replacement Tracking link. */
  navigateToReplacementDashboard: () => Promise<void>;

  /**
   * Configure replacement tracking on an asset via the asset detail panel.
   * Assumes the user is on the /assets page.
   */
  configureTracking: (assetName: string, lifespanYears: number, installDate: string) => Promise<void>;

  /**
   * Fill and submit the Record Replacement form.
   * Assumes the Record Replacement form is already open in the asset detail panel.
   */
  recordReplacement: (
    installedAt: string,
    options?: { lifespanYears?: number; cost?: string; notes?: string },
  ) => Promise<void>;
}

export const test = base.extend<ReplacementTrackingFixtures>({
  navigateToReplacementDashboard: async ({ page }, use) => {
    const fn = async () => {
      await page.getByRole('link', { name: 'Replacement Tracking' }).click();
      await page.waitForURL('**/replacement-tracking');
      await expect(page.locator('[data-flux-heading]', { hasText: 'Replacement Tracking' })).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },

  configureTracking: async ({ page, selectAsset }, use) => {
    const fn = async (assetName: string, lifespanYears: number, installDate: string) => {
      await selectAsset(assetName);

      // Click Set Up in the Replacement section
      await page.getByRole('button', { name: 'Set Up' }).click();
      await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).toBeVisible();

      // Fill Expected Lifespan (Years) — clear first then fill
      const lifespanField = page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' });
      await lifespanField.clear();
      await lifespanField.fill(String(lifespanYears));

      // Fill Installation Date
      await page.getByLabel('Installation Date').fill(installDate);

      // Save
      await page.getByRole('button', { name: 'Save' }).click();

      // Wait for form to disappear and replacement info to appear
      await expect(page.locator('[data-flux-heading]', { hasText: 'Configure Replacement Tracking' })).not.toBeVisible({ timeout: 10000 });
      await expect(page.getByText(/years remaining|years overdue/)).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },

  recordReplacement: async ({ page }, use) => {
    const fn = async (
      installedAt: string,
      options?: { lifespanYears?: number; cost?: string; notes?: string },
    ) => {
      await expect(page.locator('[data-flux-heading]', { hasText: 'Record Replacement' })).toBeVisible();

      // Fill New Installation Date
      await page.getByLabel('New Installation Date').fill(installedAt);

      // Fill optional Expected Lifespan (Years)
      if (options?.lifespanYears !== undefined) {
        const lifespanField = page.getByRole('spinbutton', { name: 'Expected Lifespan (Years)' });
        await lifespanField.clear();
        await lifespanField.fill(String(options.lifespanYears));
      }

      // Fill optional Replacement Cost
      if (options?.cost !== undefined) {
        await page.getByLabel('Replacement Cost (optional)').fill(options.cost);
      }

      // Fill optional Notes
      if (options?.notes !== undefined) {
        await page.getByLabel('Notes (optional)').fill(options.notes);
      }

      // Save
      await page.getByRole('button', { name: 'Save' }).click();

      // Wait for form to close and history to appear
      await expect(page.getByText('Replacement History')).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },
});

export { expect };
