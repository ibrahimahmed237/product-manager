<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Add Product</h1>
      <div class="space-x-4">
        <button
          @click="saveProduct"
          class="bg-green-500 text-white px-4 py-2 rounded"
        >
          Save
        </button>
        <button
          @click="$router.push('/')"
          class="bg-gray-500 text-white px-4 py-2 rounded"
        >
          Cancel
        </button>
      </div>
    </div>

    <div
      v-if="error"
      class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      role="alert"
    >
      <span class="block sm:inline">{{ error }}</span>
    </div>

    <form
      id="product_form"
      @submit.prevent="saveProduct"
      class="max-w-2xl mx-auto"
    >
      <div class="space-y-6">
        <div>
          <label for="sku" class="block text-sm font-medium text-gray-700"
            >SKU</label
          >
          <input
            id="sku"
            v-model="product.sku"
            type="text"
            required
            class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            @input="validateField('sku')"
          />
          <span v-if="validationErrors.sku" class="text-red-500 text-sm">{{
            validationErrors.sku
          }}</span>
        </div>

        <div>
          <label for="name" class="block text-sm font-medium text-gray-700"
            >Name</label
          >
          <input
            id="name"
            v-model="product.name"
            type="text"
            required
            class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            @input="validateField('name')"
          />
          <span v-if="validationErrors.name" class="text-red-500 text-sm">{{
            validationErrors.name
          }}</span>
        </div>

        <div>
          <label for="price" class="block text-sm font-medium text-gray-700"
            >Price ($)</label
          >
          <input
            id="price"
            v-model="product.price"
            type="number"
            step="0.01"
            required
            class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            @input="validateField('price')"
          />
          <span v-if="validationErrors.price" class="text-red-500 text-sm">{{
            validationErrors.price
          }}</span>
        </div>

        <div>
          <label
            for="productType"
            class="block text-sm font-medium text-gray-700"
            >Type Switcher</label
          >
          <select
            id="productType"
            v-model="product.type"
            required
            class="mt-1 block w-full rounded border-gray-300 shadow-sm"
            @change="handleTypeChange"
          >
            <option value="">Type Switcher</option>
            <option value="DVD">DVD</option>
            <option value="Book">Book</option>
            <option value="Furniture">Furniture</option>
          </select>
        </div>

        <!-- Dynamic attributes based on product type -->
        <div v-if="product.type === 'DVD'" class="space-y-4">
          <p class="text-sm text-gray-600">Please, provide size in MB</p>
          <div>
            <label for="size" class="block text-sm font-medium text-gray-700"
              >Size (MB)</label
            >
            <input
              id="size"
              v-model="product.size"
              type="number"
              required
              class="mt-1 block w-full rounded border-gray-300 shadow-sm"
              @input="validateField('size')"
            />
            <span v-if="validationErrors.size" class="text-red-500 text-sm">{{
              validationErrors.size
            }}</span>
          </div>
        </div>

        <div v-if="product.type === 'Book'" class="space-y-4">
          <p class="text-sm text-gray-600">Please, provide weight in KG</p>
          <div>
            <label for="weight" class="block text-sm font-medium text-gray-700"
              >Weight (KG)</label
            >
            <input
              id="weight"
              v-model="product.weight"
              type="number"
              step="0.01"
              required
              class="mt-1 block w-full rounded border-gray-300 shadow-sm"
              @input="validateField('weight')"
            />
            <span v-if="validationErrors.weight" class="text-red-500 text-sm">{{
              validationErrors.weight
            }}</span>
          </div>
        </div>

        <div v-if="product.type === 'Furniture'" class="space-y-4">
          <p class="text-sm text-gray-600">Please, provide dimensions</p>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label
                for="height"
                class="block text-sm font-medium text-gray-700"
                >Height (CM)</label
              >
              <input
                id="height"
                v-model="product.dimensions.height"
                type="number"
                required
                class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                @input="validateField('dimensions.height')"
              />
              <span
                v-if="validationErrors['dimensions.height']"
                class="text-red-500 text-sm"
              >
                {{ validationErrors["dimensions.height"] }}
              </span>
            </div>
            <div>
              <label for="width" class="block text-sm font-medium text-gray-700"
                >Width (CM)</label
              >
              <input
                id="width"
                v-model="product.dimensions.width"
                type="number"
                required
                class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                @input="validateField('dimensions.width')"
              />
              <span
                v-if="validationErrors['dimensions.width']"
                class="text-red-500 text-sm"
              >
                {{ validationErrors["dimensions.width"] }}
              </span>
            </div>
            <div>
              <label
                for="length"
                class="block text-sm font-medium text-gray-700"
                >Length (CM)</label
              >
              <input
                id="length"
                v-model="product.dimensions.length"
                type="number"
                required
                class="mt-1 block w-full rounded border-gray-300 shadow-sm"
                @input="validateField('dimensions.length')"
              />
              <span
                v-if="validationErrors['dimensions.length']"
                class="text-red-500 text-sm"
              >
                {{ validationErrors["dimensions.length"] }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
  name: "AddProduct",
  setup() {
    const router = useRouter();
    const error = ref("");
    const validationErrors = reactive({});

    const product = reactive({
      sku: "",
      name: "",
      price: "",
      type: "",
      size: "",
      weight: "",
      dimensions: {
        height: "",
        width: "",
        length: "",
      },
    });

    // Validation rules
    const validationRules = {
      sku: {
        required: true,
        maxLength: 64,
        pattern: /^[A-Za-z0-9-_]+$/,
        message:
          "SKU must be alphanumeric and can contain dashes and underscores",
      },
      name: {
        required: true,
        maxLength: 255,
        pattern: /^[A-Za-z0-9\s-_]+$/,
        message:
          "Name must contain only letters, numbers, spaces, dashes and underscores",
      },
      price: {
        required: true,
        min: 0.01,
        max: 999999.99,
        message: "Price must be between 0.01 and 999999.99",
      },
      size: {
        min: 1,
        max: 999999,
        message: "Size must be between 1 and 999999 MB",
      },
      weight: {
        min: 0.01,
        max: 999.99,
        message: "Weight must be between 0.01 and 999.99 KG",
      },
      dimensions: {
        min: 1,
        max: 999,
        message: "Dimensions must be between 1 and 999 CM",
      },
    };

    const validateField = (fieldName) => {
      const rules = validationRules[fieldName.split(".")[0]];
      if (!rules) return true;

      let value = fieldName.includes(".")
        ? product.dimensions[fieldName.split(".")[1]]
        : product[fieldName];

      // Clear previous error
      if (fieldName.includes(".")) {
        delete validationErrors[fieldName];
      } else {
        delete validationErrors[fieldName];
      }

      // Required check
      if (rules.required && !value) {
        validationErrors[fieldName] = "This field is required";
        return false;
      }

      // Pattern check for text fields
      if (rules.pattern && !rules.pattern.test(value)) {
        validationErrors[fieldName] = rules.message;
        return false;
      }

      // Length check for text fields
      if (rules.maxLength && value.length > rules.maxLength) {
        validationErrors[
          fieldName
        ] = `Maximum length is ${rules.maxLength} characters`;
        return false;
      }

      // Range check for numeric fields
      if (rules.min !== undefined && rules.max !== undefined) {
        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue < rules.min || numValue > rules.max) {
          validationErrors[fieldName] = rules.message;
          return false;
        }
      }

      return true;
    };

    const handleTypeChange = () => {
      // Reset type-specific fields when type changes
      product.size = "";
      product.weight = "";
      product.dimensions.height = "";
      product.dimensions.width = "";
      product.dimensions.length = "";

      // Clear related validation errors
      delete validationErrors.size;
      delete validationErrors.weight;
      delete validationErrors["dimensions.height"];
      delete validationErrors["dimensions.width"];
      delete validationErrors["dimensions.length"];
    };

    const validateForm = () => {
      let isValid = true;

      // Validate common fields
      ["sku", "name", "price"].forEach((field) => {
        if (!validateField(field)) {
          isValid = false;
        }
      });

      // Validate type-specific fields
      if (product.type === "DVD") {
        if (!validateField("size")) isValid = false;
      } else if (product.type === "Book") {
        if (!validateField("weight")) isValid = false;
      } else if (product.type === "Furniture") {
        ["height", "width", "length"].forEach((dim) => {
          if (!validateField(`dimensions.${dim}`)) isValid = false;
        });
      }

      if (!product.type) {
        validationErrors.type = "Product type is required";
        isValid = false;
      }

      return isValid;
    };

    const saveProduct = async () => {
  error.value = "";

  if (!validateForm()) {
    error.value = "Please, submit required data";
    return;
  }

  try {
    // Prepare the data payload
    const payload = { ...product };

    // If product type is "Furniture", include dimensions separately
    if (product.type === "Furniture") {
      payload.height = product.dimensions.height;
      payload.width = product.dimensions.width;
      payload.length = product.dimensions.length;

      // Remove the dimensions object as we now have the separate properties
      delete payload.dimensions;
    }

    await axios.post(`${import.meta.env.VITE_API_URL}/products`, payload);
    router.push("/");
  } catch (err) {
    if (err.response?.data?.message === "SKU already exists") {
      error.value = "SKU must be unique";
    } else {
      error.value = "Please, provide the data of indicated type";
    }
  }
};


    return {
      product,
      error,
      validationErrors,
      saveProduct,
      validateField,
      handleTypeChange,
    };
  },
};
</script>
