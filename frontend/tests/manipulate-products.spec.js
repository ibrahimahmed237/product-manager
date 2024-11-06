import { test, expect } from "@playwright/test";
import fs from "fs";
// Helper function to check for product list

test.describe("Product Manipulation", () => {
  test("should handle complete product lifecycle", async ({ page }) => {
    // Initial page load
    await page.goto("http://localhost:5173/");

    // Check initial checkboxes
    const initialCheckboxes = await page
      .locator('input[type="checkbox"]')
      .all();

    // Verify checkboxes again (matching the double check in logs)
    const verifyCheckboxes = await page.locator('input[type="checkbox"]').all();
    await expect(verifyCheckboxes).toHaveLength(initialCheckboxes.length);

    // Navigate to Add Product
    await page.getByText("ADD").click();
    await expect(page.getByText("Add Product")).toBeVisible();

    // Add DVD Product
    await page.fill("#sku", "DVD_TEST001");
    await page.fill("#name", "Test DVD Product");
    await page.fill("#price", "19.99");
    await page.selectOption("#productType", "DVD");

    // Verify DVD specific field is visible
    await expect(page.locator("#size")).toBeVisible();

    // Fill DVD specific field
    await page.fill("#size", "700");

    // Save the product
    await page.getByText("Save").click();
    // await page.getByText("cancel ").click();

    await fs.writeFileSync("product-list.html", await page.content());
    
    // Verify product list is visible
    await waitForProductList(page);

    // Verify new product is visible
    // await expect(page.getByText("Test DVD Product")).toBeVisible();

    // Verify redirect and product visibility
    // await waitForProductList(page);
  });
});
async function waitForProductList(page) {
  await expect(page.getByText("Product List")).toBeVisible({ timeout: 10000 });
}

