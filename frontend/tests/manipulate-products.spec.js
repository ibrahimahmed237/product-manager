import { test, expect } from '@playwright/test';

// Helper function to fill common fields
async function fillCommonFields(page, { sku, name, price }) {
  await page.fill('#sku', sku);
  await page.fill('#name', name);
  await page.fill('#price', price);
}

// Helper function to verify successful product creation
async function verifyProductCreation(page, productName) {
  await expect(page.getByText('Product List')).toBeVisible({ timeout: 10000 });
  await expect(page.getByText(productName)).toBeVisible({ timeout: 10000 });
}

test.describe('Product Form Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('https://product-manager-test-1.netlify.app/add-product');
  });

  test('should create DVD product successfully', async ({ page }) => {
    await fillCommonFields(page, {
      sku: 'DVD123',
      name: 'Test DVD',
      price: '15.99'
    });
    await page.selectOption('#productType', 'DVD');
    await page.fill('#size', '700');
    await page.click('button:has-text("Save")');
    await verifyProductCreation(page, 'Test DVD');
  });

  test('should create Book product successfully', async ({ page }) => {
    await fillCommonFields(page, {
      sku: 'BOOK123',
      name: 'Test Book',
      price: '29.99'
    });
    await page.selectOption('#productType', 'Book');
    await page.fill('#weight', '1.5');
    await page.click('button:has-text("Save")');
    await verifyProductCreation(page, 'Test Book');
  });

  test('should create Furniture product successfully', async ({ page }) => {
    await fillCommonFields(page, {
      sku: 'FRN123',
      name: 'Test Furniture',
      price: '199.99'
    });
    await page.selectOption('#productType', 'Furniture');
    await page.fill('#height', '100');
    await page.fill('#width', '50');
    await page.fill('#length', '75');
    await page.click('button:has-text("Save")');
    await verifyProductCreation(page, 'Test Furniture');
  });

  test('should show validation errors for invalid inputs', async ({ page }) => {
    // Test invalid SKU
    await page.fill('#sku', '@@invalid@@');
    await page.click('#name'); // Trigger validation
    await expect(page.getByText('SKU must be alphanumeric and can contain dashes and underscores')).toBeVisible();

    // Test invalid price
    await page.fill('#price', '-10');
    await page.click('#name'); // Trigger validation
    await expect(page.getByText('Price must be between 0.01 and 999999.99')).toBeVisible();

    // Test invalid DVD size
    await page.selectOption('#productType', 'DVD');
    await page.fill('#size', '0');
    await page.click('#name'); // Trigger validation
    await expect(page.getByText('Size must be between 1 and 999999 MB')).toBeVisible();
  });

  test('should handle duplicate SKU error', async ({ page }) => {
    // First product
    await fillCommonFields(page, {
      sku: 'DUPLICATE123',
      name: 'First Product',
      price: '10.00'
    });
    await page.selectOption('#productType', 'DVD');
    await page.fill('#size', '500');
    await page.click('button:has-text("Save")');
    await verifyProductCreation(page, 'First Product');

    // Try to create second product with same SKU
    await page.goto('https://product-manager-test-1.netlify.app/add-product');
    await fillCommonFields(page, {
      sku: 'DUPLICATE123',
      name: 'Second Product',
      price: '20.00'
    });
    await page.selectOption('#productType', 'DVD');
    await page.fill('#size', '300');
    await page.click('button:has-text("Save")');
    await expect(page.getByText('SKU must be unique')).toBeVisible();
  });

  test('should clear type-specific fields when type changes', async ({ page }) => {
    // Fill DVD fields
    await page.selectOption('#productType', 'DVD');
    await page.fill('#size', '500');

    // Change to Book
    await page.selectOption('#productType', 'Book');
    
    // Verify DVD field is cleared
    await expect(page.locator('#size')).toHaveValue('');
    
    // Fill Book field
    await page.fill('#weight', '2.5');

    // Change to Furniture
    await page.selectOption('#productType', 'Furniture');
    
    // Verify Book field is cleared
    await expect(page.locator('#weight')).toHaveValue('');
  });

  test('should handle form submission with empty required fields', async ({ page }) => {
    await page.click('button:has-text("Save")');
    await expect(page.getByText('Please, submit required data')).toBeVisible();
  });
});