import { test as base, expect } from './fixtures';

export interface ServiceRecordData {
  date: string;
  type: string;
  description: string;
  provider?: string;
  cost?: string;
  underWarranty?: boolean;
  warrantyExpires?: string;
}

export interface ServiceRecordFixtures {
  /** Navigate to the per-asset service records page via the asset list -> detail panel -> View Service Records link. */
  goToAssetServiceRecords: (assetName: string) => Promise<void>;

  /** Fill and submit the service record creation form. Assumes the service records page is open. */
  createServiceRecord: (data: ServiceRecordData) => Promise<void>;
}

export const test = base.extend<ServiceRecordFixtures>({
  goToAssetServiceRecords: async ({ page, selectAsset }, use) => {
    const fn = async (assetName: string) => {
      await selectAsset(assetName);
      await page.getByRole('link', { name: 'View Service Records' }).click();
      await page.waitForURL(/\/assets\/\d+\/service-records/);
      await expect(page.locator('[data-flux-heading]', { hasText: 'Service Records' })).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },

  createServiceRecord: async ({ page }, use) => {
    const fn = async (data: ServiceRecordData) => {
      // Open the create form
      await page.getByRole('button', { name: 'Add Service Record' }).click();
      await expect(page.locator('[data-flux-heading]', { hasText: 'New Service Record' })).toBeVisible();

      // Fill required fields
      await page.getByLabel('Service Date').fill(data.date);
      await page.getByLabel('Service Type').selectOption(data.type);
      await page.getByLabel('Description').fill(data.description);

      // Fill optional fields
      if (data.provider) {
        await page.getByLabel('Provider / Contractor').fill(data.provider);
      }
      if (data.cost !== undefined) {
        await page.getByLabel('Cost').fill(data.cost);
      }
      if (data.underWarranty) {
        await page.getByLabel('Under Warranty').check();
        // Wait for Livewire to show the warranty expires field
        await expect(page.getByLabel('Warranty Expires')).toBeVisible();
        if (data.warrantyExpires) {
          await page.getByLabel('Warranty Expires').fill(data.warrantyExpires);
        }
      }

      await page.getByRole('button', { name: 'Save Record' }).click();

      // Wait for the record to appear in the list
      await expect(page.getByText(data.description).first()).toBeVisible({ timeout: 10000 });
    };

    await use(fn);
  },
});

export { expect };
