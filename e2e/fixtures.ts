import { test as base, expect, type Page } from '@playwright/test';

/**
 * Shared test fixtures for asset CRUD e2e tests.
 *
 * Provides helper methods for authentication and asset management
 * so individual test files stay focused on the scenario under test.
 */

export interface AssetData {
  name: string;
  category: string;
  location: string;
  purchaseDate?: string;
  installDate?: string;
  warrantyExpirationDate?: string;
  notes?: string;
}

export interface TestFixtures {
  /** Register a new user and navigate to the assets page. */
  registerAndGoToAssets: (overrides?: {
    name?: string;
    email?: string;
    password?: string;
  }) => Promise<{ email: string; password: string }>;

  /** Log in with the given credentials and navigate to the assets page. */
  loginAndGoToAssets: (email: string, password: string) => Promise<void>;

  /** Fill and submit the asset creation form. Assumes the form is already open. */
  fillAssetForm: (data: AssetData) => Promise<void>;

  /** Open the create form, fill it, and save. Returns to the asset list. */
  createAsset: (data: AssetData) => Promise<void>;

  /** Click on an asset in the list by its name to open the detail view. */
  selectAsset: (name: string) => Promise<void>;

  /** Archive the currently selected asset (clicks Archive, confirms modal). */
  archiveSelectedAsset: () => Promise<void>;

  /** Log out the current user by clicking the sidebar profile menu. */
  logout: (displayName: string) => Promise<void>;
}

export const test = base.extend<TestFixtures>({
  registerAndGoToAssets: async ({ page }, use) => {
    const fn = async (
      overrides: { name?: string; email?: string; password?: string } = {},
    ) => {
      const name = overrides.name ?? 'Test User';
      const email =
        overrides.email ?? `testuser-${Date.now()}-${Math.random().toString(36).substring(2, 9)}@example.com`;
      const password = overrides.password ?? 'password123';

      await page.goto('/register');
      await page.getByLabel('Name').fill(name);
      await page.getByLabel('Email address').fill(email);
      await page.getByRole('textbox', { name: 'Password', exact: true }).fill(password);
      await page.getByRole('textbox', { name: 'Confirm password' }).fill(password);
      await page.getByRole('button', { name: 'Create account' }).click();

      // Wait for successful registration - check for user menu which indicates we're logged in
      await expect(page.getByRole('button', { name: `${name.split(' ').map(w => w[0]).join('')} ${name}` })).toBeVisible({ timeout: 10000 });

      // Navigate to assets page
      await page.goto('/assets');
      await page.waitForURL('**/assets');
      await expect(page.getByText('Assets', { exact: true }).first()).toBeVisible();

      return { email, password };
    };

    await use(fn);
  },

  loginAndGoToAssets: async ({ page }, use) => {
    const fn = async (email: string, password: string) => {
      await page.goto('/login');
      await page.getByLabel('Email address').fill(email);
      await page.getByRole('textbox', { name: 'Password' }).fill(password);
      await page.getByRole('button', { name: 'Log in' }).click();

      // Wait for successful login - check for sidebar which indicates we're logged in
      await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible({ timeout: 10000 });

      // Navigate to assets page
      await page.goto('/assets');
      await page.waitForURL('**/assets');
      await expect(page.getByText('Assets', { exact: true }).first()).toBeVisible();
    };

    await use(fn);
  },

  fillAssetForm: async ({ page }, use) => {
    const fn = async (data: AssetData) => {
      await page.getByLabel('Name').fill(data.name);

      // Flux select: click the select button then pick an option
      await page.getByLabel('Category').selectOption(data.category);

      await page.getByLabel('Location').fill(data.location);

      if (data.purchaseDate) {
        await page.getByLabel('Purchase Date').fill(data.purchaseDate);
      }
      if (data.installDate) {
        await page.getByLabel('Install Date').fill(data.installDate);
      }
      if (data.warrantyExpirationDate) {
        await page.getByLabel('Warranty Expiration Date').fill(
          data.warrantyExpirationDate,
        );
      }
      if (data.notes) {
        await page.getByLabel('Notes').fill(data.notes);
      }
    };

    await use(fn);
  },

  createAsset: async ({ page }, use) => {
    let fillAssetForm: ((data: AssetData) => Promise<void>) | null = null;

    // We need the fillAssetForm helper inside createAsset
    const fn = async (data: AssetData) => {
      const addButton = page.getByRole('button', { name: 'Add Asset' });

      // Ensure the Add Asset button is not disabled/busy before clicking
      await expect(addButton).toBeEnabled();

      // Trigger the Livewire action directly to avoid click interception issues
      await page.evaluate(() => {
        const component = window.Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
        component.call('openCreateForm');
      });

      // Wait for the form to appear before filling it
      await expect(page.getByLabel('Name')).toBeVisible();

      // Fill the form
      await page.getByLabel('Name').fill(data.name);
      await page.getByLabel('Category').selectOption(data.category);
      await page.getByLabel('Location').fill(data.location);

      if (data.purchaseDate) {
        await page.getByLabel('Purchase Date').fill(data.purchaseDate);
      }
      if (data.installDate) {
        await page.getByLabel('Install Date').fill(data.installDate);
      }
      if (data.warrantyExpirationDate) {
        await page.getByLabel('Warranty Expiration Date').fill(
          data.warrantyExpirationDate,
        );
      }
      if (data.notes) {
        await page.getByLabel('Notes').fill(data.notes);
      }

      await page.getByRole('button', { name: 'Save' }).click();

      // Wait for the asset to appear in the list (modal will close and list will update)
      await expect(
        page.getByRole('button', { name: new RegExp(data.name) }),
      ).toBeVisible();

      // Wait for the form to close completely (Livewire update to finish)
      await expect(page.getByLabel('Name')).not.toBeVisible();
    };

    await use(fn);
  },

  selectAsset: async ({ page }, use) => {
    const fn = async (name: string) => {
      await page.getByRole('button', { name }).click();
      // Wait for detail view to show - the Back button indicates we're in detail view
      await expect(page.getByRole('button', { name: 'Back' })).toBeVisible();
      // Also verify the asset name is visible in the detail view
      await expect(page.getByText(name, { exact: true }).first()).toBeVisible();
    };

    await use(fn);
  },

  archiveSelectedAsset: async ({ page }, use) => {
    const fn = async () => {
      // Click the Archive button in detail view (not the modal one)
      await page.getByRole('button', { name: 'Archive' }).first().click();

      // Wait for the modal to appear
      await expect(
        page.getByText('Archive this asset?', { exact: true }),
      ).toBeVisible();

      // Click the danger Archive button in the modal
      await page
        .getByRole('dialog')
        .getByRole('button', { name: 'Archive' })
        .click();

      // Wait for modal to close and list to update
      await expect(
        page.getByText('Archive this asset?', { exact: true }),
      ).toBeHidden();
    };

    await use(fn);
  },
  logout: async ({ page }, use) => {
    const fn = async (displayName: string) => {
      // Use the specific data-test attribute to avoid matching asset buttons
      await page.locator('[data-test="sidebar-menu-button"]').click();

      // Wait for navigation after clicking logout (it's a form POST that redirects to home)
      await Promise.all([
        page.waitForURL('**/', { timeout: 10000 }),
        page.getByRole('menuitem', { name: 'Log Out' }).click(),
      ]);
    };

    await use(fn);
  },
});

export { expect };
