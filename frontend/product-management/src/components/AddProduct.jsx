import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const AddProduct = () => {
  const navigate = useNavigate();
  const [error, setError] = useState('');
  const [validationErrors, setValidationErrors] = useState({});
  const [isFormSubmitted, setIsFormSubmitted] = useState(false);
  const [product, setProduct] = useState({
    sku: '',
    name: '',
    price: '',
    type: '',
    size: '',
    weight: '',
    dimensions: {
      height: '',
      width: '',
      length: '',
    },
  });

  const validationRules = {
    sku: {
      required: true,
      maxLength: 64,
      pattern: /^[A-Za-z0-9-_]+$/,
      message: 'SKU must be alphanumeric and can contain dashes and underscores',
    },
    name: {
      required: true,
      maxLength: 255,
      pattern: /^[A-Za-z0-9\s-_]+$/,
      message: 'Name must contain only letters, numbers, spaces, dashes and underscores',
    },
    price: {
      required: true,
      min: 0.01,
      max: 999999.99,
      message: 'Price must be between 0.01 and 999999.99',
    },
    size: {
      min: 1,
      max: 999999,
      message: 'Size must be between 1 and 999999 MB',
    },
    weight: {
      min: 0.01,
      max: 999.99,
      message: 'Weight must be between 0.01 and 999.99 KG',
    },
    dimensions: {
      min: 1,
      max: 999,
      message: 'Dimensions must be between 1 and 999 CM',
    },
  };

  // Only validate when form has been submitted
  useEffect(() => {
    if (isFormSubmitted) {
      validateForm(false);
    }
  }, [product, isFormSubmitted]);

  const validateField = (fieldName, value) => {
    const rules = validationRules[fieldName.split('.')[0]];
    if (!rules) return true;

    // Don't validate empty fields unless they're required
    if (!value && !rules.required) {
      setValidationErrors(prev => {
        const { [fieldName]: _, ...rest } = prev;
        return rest;
      });
      return true;
    }

    let isValid = true;
    let errorMessage = '';

    // Required field validation
    if (rules.required && !value) {
      isValid = false;
      errorMessage = 'This field is required';
    }
    // Pattern validation (for text fields)
    else if (rules.pattern && value && !rules.pattern.test(value)) {
      isValid = false;
      errorMessage = rules.message;
    }
    // Length validation
    else if (rules.maxLength && value.length > rules.maxLength) {
      isValid = false;
      errorMessage = `Maximum length is ${rules.maxLength} characters`;
    }
    // Number range validation
    else if (rules.min !== undefined && rules.max !== undefined) {
      const numValue = parseFloat(value);
      if (isNaN(numValue) || numValue < rules.min || numValue > rules.max) {
        isValid = false;
        errorMessage = rules.message;
      }
    }

    // Update validation errors
    setValidationErrors(prev => {
      if (isValid) {
        const { [fieldName]: _, ...rest } = prev;
        return rest;
      }
      return { ...prev, [fieldName]: errorMessage };
    });

    return isValid;
  };

  const handleTypeChange = (e) => {
    const newType = e.target.value;
    setProduct(prev => ({
      ...prev,
      type: newType,
      size: '',
      weight: '',
      dimensions: {
        height: '',
        width: '',
        length: '',
      },
    }));
    if (isFormSubmitted) {
      setValidationErrors({});
    }
  };

  const validateForm = (showTypeError = true) => {
    let isValid = true;

    // Validate required fields
    ['sku', 'name', 'price'].forEach((field) => {
      if (!validateField(field, product[field])) {
        isValid = false;
      }
    });

    // Validate type-specific fields
    if (product.type === 'DVD') {
      if (!validateField('size', product.size)) isValid = false;
    } else if (product.type === 'Book') {
      if (!validateField('weight', product.weight)) isValid = false;
    } else if (product.type === 'Furniture') {
      ['height', 'width', 'length'].forEach((dim) => {
        if (!validateField(`dimensions.${dim}`, product.dimensions[dim])) isValid = false;
      });
    }

    // Only show type error during form submission
    if (showTypeError && !product.type) {
      setValidationErrors(prev => ({
        ...prev,
        type: 'Product type is required'
      }));
      isValid = false;
    }

    return isValid;
  };

  const handleInputChange = (e, dimensionField = null) => {
    const { name, value } = e.target;
  
    if (dimensionField) {
      setProduct(prev => ({
        ...prev,
        dimensions: {
          ...prev.dimensions,
          [dimensionField]: value
        }
      }));
    } else {
      setProduct(prev => ({
        ...prev,
        [name]: value
      }));
    }
  };

  const saveProduct = async (e) => {
    e.preventDefault();
    setError('');
    setIsFormSubmitted(true);

    if (!validateForm(true)) {
      setError('Please, submit required data');
      return;
    }

    try {
      const payload = { ...product };

      if (product.type === 'Furniture') {
        payload.height = product.dimensions.height;
        payload.width = product.dimensions.width;
        payload.length = product.dimensions.length;
        delete payload.dimensions;
      }

      await axios.post(`${import.meta.env.VITE_API_URL}/products`, payload);
      navigate('/');
    } catch (err) {
      if (err.response?.data?.message === 'SKU already exists') {
        setError('SKU must be unique');
      } else {
        setError('Please, submit required data');
      }
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Add Product</h1>
        <div className="space-x-4">
          <button
            onClick={saveProduct}
            className="bg-green-500 text-white px-4 py-2 rounded"
          >
            Save
          </button>
          <button
            onClick={() => navigate('/')}
            className="bg-gray-500 text-white px-4 py-2 rounded"
          >
            Cancel
          </button>
        </div>
      </div>

      {error && (
        <div
          className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
          role="alert"
        >
          <span className="block sm:inline">{error}</span>
        </div>
      )}

      <form
        id="product_form"
        onSubmit={saveProduct}
        className="max-w-2xl mx-auto"
      >
        <div className="space-y-6">
          <div>
            <label htmlFor="sku" className="block text-sm font-medium text-gray-700">
              SKU
            </label>
            <input
              id="sku"
              name="sku"
              value={product.sku}
              onChange={handleInputChange}
              type="text"
              required
              className="mt-1 block w-full rounded border-gray-300 shadow-sm"
            />
            {validationErrors.sku && (
              <span className="text-red-500 text-sm">{validationErrors.sku}</span>
            )}
          </div>

          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700">
              Name
            </label>
            <input
              id="name"
              name="name"
              value={product.name}
              onChange={handleInputChange}
              type="text"
              required
              className="mt-1 block w-full rounded border-gray-300 shadow-sm"
            />
            {validationErrors.name && (
              <span className="text-red-500 text-sm">{validationErrors.name}</span>
            )}
          </div>

          <div>
            <label htmlFor="price" className="block text-sm font-medium text-gray-700">
              Price ($)
            </label>
            <input
              id="price"
              name="price"
              value={product.price}
              onChange={handleInputChange}
              type="number"
              step="0.01"
              required
              className="mt-1 block w-full rounded border-gray-300 shadow-sm"
            />
            {validationErrors.price && (
              <span className="text-red-500 text-sm">{validationErrors.price}</span>
            )}
          </div>

          <div>
            <label htmlFor="productType" className="block text-sm font-medium text-gray-700">
              Type Switcher
            </label>
            <select
              id="productType"
              name="type"
              value={product.type}
              onChange={handleTypeChange}
              required
              className="mt-1 block w-full rounded border-gray-300 shadow-sm"
            >
              <option value="">Type Switcher</option>
              <option value="DVD">DVD</option>
              <option value="Book">Book</option>
              <option value="Furniture">Furniture</option>
            </select>
            {validationErrors.type && (
              <span className="text-red-500 text-sm">{validationErrors.type}</span>
            )}
          </div>

          {product.type === 'DVD' && (
            <div className="space-y-4">
              <p className="text-sm text-gray-600">Please, provide size in MB</p>
              <div>
                <label htmlFor="size" className="block text-sm font-medium text-gray-700">
                  Size (MB)
                </label>
                <input
                  id="size"
                  name="size"
                  value={product.size}
                  onChange={handleInputChange}
                  type="number"
                  required
                  className="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                {validationErrors.size && (
                  <span className="text-red-500 text-sm">{validationErrors.size}</span>
                )}
              </div>
            </div>
          )}

          {product.type === 'Book' && (
            <div className="space-y-4">
              <p className="text-sm text-gray-600">Please, provide weight in KG</p>
              <div>
                <label htmlFor="weight" className="block text-sm font-medium text-gray-700">
                  Weight (KG)
                </label>
                <input
                  id="weight"
                  name="weight"
                  value={product.weight}
                  onChange={handleInputChange}
                  type="number"
                  step="0.01"
                  required
                  className="mt-1 block w-full rounded border-gray-300 shadow-sm"
                />
                {validationErrors.weight && (
                  <span className="text-red-500 text-sm">{validationErrors.weight}</span>
                )}
              </div>
            </div>
          )}

          {product.type === 'Furniture' && (
            <div className="space-y-4">
              <p className="text-sm text-gray-600">Please, provide dimensions</p>
              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label htmlFor="height" className="block text-sm font-medium text-gray-700">
                    Height (CM)
                  </label>
                  <input
                    id="height"
                    value={product.dimensions.height}
                    onChange={(e) => handleInputChange(e, 'height')}
                    type="number"
                    required
                    className="mt-1 block w-full rounded border-gray-300 shadow-sm"
                  />
                  {validationErrors['dimensions.height'] && (
                    <span className="text-red-500 text-sm">
                      {validationErrors['dimensions.height']}
                    </span>
                  )}
                </div>
                <div>
                  <label htmlFor="width" className="block text-sm font-medium text-gray-700">
                    Width (CM)
                  </label>
                  <input
                    id="width"
                    value={product.dimensions.width}
                    onChange={(e) => handleInputChange(e, 'width')}
                    type="number"
                    required
                    className="mt-1 block w-full rounded border-gray-300 shadow-sm"
                  />
                  {validationErrors['dimensions.width'] && (
                    <span className="text-red-500 text-sm">
                      {validationErrors['dimensions.width']}
                    </span>
                  )}
                </div>
                <div>
                  <label htmlFor="length" className="block text-sm font-medium text-gray-700">
                    Length (CM)
                  </label>
                  <input
                    id="length"
                    value={product.dimensions.length}
                    onChange={(e) => handleInputChange(e, 'length')}
                    type="number"
                    required
                    className="mt-1 block w-full rounded border-gray-300 shadow-sm"
                  />
                  {validationErrors['dimensions.length'] && (
                    <span className="text-red-500 text-sm">
                      {validationErrors['dimensions.length']}
                    </span>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      </form>
    </div>
  );
};

export default AddProduct;