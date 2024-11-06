import { test, expect } from "@playwright/test";
import fs from "fs";
// Helper function to check for product list

test.describe("Product manipulation", () => {
  test("Remove and add different products", async ({ page }) => {
    await page.goto("http://localhost:5173/", {
      timeout: 3000,
    });

    const checkboxes = await page.locator('input[type="checkbox"]').all();

    if (checkboxes.length !== 0) {
      await deleteProducts(page);
    }

    const updatedCheckboxes = await page
      .locator('input[type="checkbox"]')
      .all();

    expect(updatedCheckboxes).toHaveLength(0);

    await page.getByText("ADD").click();

    await expect(page.locator("#product_form")).toBeVisible({ timeout: 10000 });

    await page.locator("#sku").fill("SKUTest000");
    await page.locator("#name").fill("NameTest000");
    await page.locator("#price").fill("25");
    await page.locator("#productType").selectOption({ label: "DVD" });

    await expect(page.locator("#size")).toBeVisible();

    await page.locator("#size").fill("200");
    await page.getByText("Save").click();

    await expect(page.getByText("Product List")).toBeVisible({ timeout: 15000 });
    await expect(page.getByText("NameTest000")).toBeVisible({ timeout: 15000 });

    await deleteProducts(page);

    await page.getByText("ADD").click();

    await expect(page.locator("#product_form")).toBeVisible({ timeout: 10000 });

    await page.locator("#sku").fill("SKUTest000");
    await page.locator("#name").fill("NameTest000");
    await page.locator("#price").fill("25");
    await page.locator("#productType").selectOption({ label: "Book" });

    await expect(page.locator("#weight")).toBeVisible();

    await page.locator("#weight").fill("200");
    await page.getByText("Save").click();

    await expect(page.getByText("Product List")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.getByText("NameTest000")).toBeVisible({ timeout: 15000 });

    await deleteProducts(page);

    await page.getByText("ADD").click();

    await expect(page.locator("#product_form")).toBeVisible({ timeout: 15000 });

    await page.locator("#sku").fill("SKUTest000");
    await page.locator("#name").fill("NameTest000");
    await page.locator("#price").fill("25");
    await page.locator("#productType").selectOption({ label: "Furniture" });

    await expect(page.locator("#height")).toBeVisible();
    await expect(page.locator("#width")).toBeVisible();
    await expect(page.locator("#length")).toBeVisible();

    await page.locator("#height").fill("200");
    await page.locator("#width").fill("200");
    await page.locator("#length").fill("200");
    await page.getByText("Save").click();

    await expect(page.getByText("Product List")).toBeVisible({
      timeout: 10000,
    });
    await expect(page.getByText("NameTest000")).toBeVisible({ timeout: 15000 });

    await deleteProducts(page);
  });

  test("add product with invalid input", async ({ page }) => {
    await page.goto("http://localhost:5173/");

    await page.getByText("ADD").click();

    await expect(page.locator("#product_form")).toBeVisible({ timeout: 15000 });

    await page.locator("#sku").fill("SKUTest000");
    await page.locator("#name").fill("NameTest000");
    await page.locator("#price").fill("25");
    await page.locator("#productType").selectOption({ label: "Furniture" });

    await expect(page.locator("#height")).toBeVisible();
    await expect(page.locator("#width")).toBeVisible();
    await expect(page.locator("#length")).toBeVisible();

    // populating only one out of the three required fields
    await page.locator("#height").fill("200");
    await page.getByText("Save").click();

    await expect(page.getByText("Product List")).toBeHidden({ timeout: 15000 });

    await deleteProducts(page);
  });
});

// Define deleteProducts function
async function deleteProducts(page) {
  // Select all checkboxes for products
  const checkboxes = await page.locator("input.delete-checkbox");

  // Check if there are any checkboxes available
  const count = await checkboxes.count();
  if (count === 0) return; // Exit if no checkboxes are found

  // Click on each checkbox to select it
  for (let i = 0; i < count; i++) {
    await checkboxes.nth(i).check();
  }

  // Click the "MASS DELETE" button to delete selected products
  const massDeleteButton = page.locator('button:has-text("MASS DELETE")');
  await massDeleteButton.click();

  // Wait for UI to reflect deletion of products
  await page.waitForTimeout(500); // Adjust the wait time as necessary

  // Optional: Confirm that no products remain, or you could check this in your test
  const remainingProducts = await page.locator("input.delete-checkbox").count();
  if (remainingProducts !== 0) {
    throw new Error("Not all products were deleted");
  }
}
