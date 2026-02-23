import { test as base, expect } from './fixtures';

export interface MaintenanceTaskData {
  name: string;
  description?: string;
  recurrenceUnit?: string;
  recurrenceCount?: number;
  startDate?: string;
}

export interface MaintenanceFixtures {
  /** Navigate to the per-asset maintenance page via the asset list → detail panel → View Maintenance link. */
  goToAssetMaintenance: (assetName: string) => Promise<void>;

  /** Fill and submit the maintenance task creation form. Assumes the per-asset maintenance page is open. */
  createMaintenanceTask: (data: MaintenanceTaskData) => Promise<void>;
}

export const test = base.extend<MaintenanceFixtures>({
  goToAssetMaintenance: async ({ page, selectAsset }, use) => {
    const fn = async (assetName: string) => {
      await selectAsset(assetName);
      await page.getByRole('link', { name: 'View Maintenance' }).click();
      await page.waitForURL(/\/assets\/\d+\/maintenance/);
      await expect(page.locator('[data-flux-heading]', { hasText: 'Maintenance Tasks' })).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },

  createMaintenanceTask: async ({ page }, use) => {
    const fn = async (data: MaintenanceTaskData) => {
      await page.getByLabel('Task Name').fill(data.name);

      if (data.description) {
        await page.getByLabel('Description').fill(data.description);
      }

      if (data.recurrenceUnit) {
        await page.getByLabel('Recurrence').selectOption(data.recurrenceUnit);
      }

      if (data.recurrenceCount !== undefined) {
        const countField = page.getByLabel('Every (count)');
        await countField.clear();
        await countField.fill(String(data.recurrenceCount));
      }

      if (data.startDate) {
        await page.getByLabel('Start Date').fill(data.startDate);
      }

      await page.getByRole('button', { name: 'Add Task' }).click();
      await expect(page.getByText(data.name).first()).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },
});

export { expect };
