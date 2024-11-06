// components/ProductList.jsx
import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const ProductList = () => {
  const [products, setProducts] = useState([]);
  const [selectedProducts, setSelectedProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const formatPrice = (price) => {
    return Number(price).toFixed(2);
  };

  const fetchProducts = async () => {
    setLoading(true);
    setError('');
    const VITE_API_URL = import.meta.env.VITE_API_URL;

    try {
      const response = await axios.get(`${VITE_API_URL}/products`);
      if (response.data.status === 'success') {
        setProducts(response.data.data);
      } else {
        setError('Failed to fetch products');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error fetching products');
      console.error('Error fetching products:', err);
    } finally {
      setLoading(false);
    }
  };

  const massDelete = async () => {
    if (selectedProducts.length === 0) return;
    
    setLoading(true);
    setError('');
    
    try {
      await axios.delete(`${import.meta.env.VITE_API_URL}/mass-delete`, {
        data: { ids: selectedProducts }
      });
      await fetchProducts();
      setSelectedProducts([]);
    } catch (err) {
      setError(err.response?.data?.message || 'Error deleting products');
      console.error('Error deleting products:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, []);

  const handleCheckboxChange = (productId) => {
    setSelectedProducts(prev => {
      if (prev.includes(productId)) {
        return prev.filter(id => id !== productId);
      } else {
        return [...prev, productId];
      }
    });
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Product List</h1>
        <div className="space-x-4">
          <button
            onClick={() => navigate('/add-product')}
            className="bg-blue-500 text-white px-4 py-2 rounded"
          >
            ADD
          </button>
          <button 
            onClick={massDelete} 
            className="bg-red-500 text-white px-4 py-2 rounded"
            disabled={selectedProducts.length === 0}
          >
            MASS DELETE
          </button>
        </div>
      </div>

      {loading && (
        <div className="text-center py-4">Loading...</div>
      )}
      
      {error && (
        <div className="text-red-500 text-center py-4">{error}</div>
      )}
      
      {!loading && !error && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {products.map(product => (
            <div 
              key={product.sku}
              className="border p-4 rounded relative"
            >
              <input
                type="checkbox"
                checked={selectedProducts.includes(product.id)}
                onChange={() => handleCheckboxChange(product.id)}
                className="delete-checkbox absolute top-4 left-4"
              />
              <div className="text-center">
                <p>{product.sku}</p>
                <p>{product.name}</p>
                <p>${formatPrice(product.price)}</p>
                {product.type === 'DVD' && (
                  <p>Size: {product.size} MB</p>
                )}
                {product.type === 'Book' && (
                  <p>Weight: {product.weight} KG</p>
                )}
                {product.type === 'Furniture' && (
                  <p>Dimensions: {product.height}x{product.width}x{product.length}</p>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default ProductList;