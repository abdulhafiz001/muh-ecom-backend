# Al-Anwar E-commerce Backend

This is the Laravel backend for the Al-Anwar e-commerce platform.

## Features

- **User Management**: Registration, login, and user profiles
- **Product Management**: CRUD operations for products with categories
- **Shopping Cart**: Add, update, and remove items from cart
- **Order Management**: Create and track orders
- **Category Management**: Organize products by categories
- **RESTful API**: Clean API endpoints for frontend integration

## Requirements

- PHP 8.2 or higher
- Composer
- SQLite (for development) or MySQL/PostgreSQL (for production)

## Installation

1. **Clone the repository and navigate to the backend directory:**
   ```bash
   cd e-comm-backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Configure database in .env file:**
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/your/project/e-comm-backend/database/database.sqlite
   ```

6. **Create SQLite database file:**
   ```bash
   touch database/database.sqlite
   ```

7. **Run database migrations:**
   ```bash
   php artisan migrate
   ```

8. **Seed the database with sample data:**
   ```bash
   php artisan db:seed
   ```

9. **Start the development server:**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

## API Endpoints

### Public Routes
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get category details
- `GET /api/products` - List all products (with search, filters, pagination)
- `GET /api/products/featured` - Get featured products
- `GET /api/products/{id}` - Get product details
- `GET /api/products/category/{categoryId}` - Get products by category

### Authentication Routes
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login

### Protected Routes (require authentication)
- `GET /api/auth/user` - Get current user
- `POST /api/auth/logout` - User logout
- `GET /api/cart` - Get user's cart
- `POST /api/cart/add` - Add item to cart
- `PUT /api/cart/{id}` - Update cart item
- `DELETE /api/cart/{id}` - Remove item from cart
- `DELETE /api/cart` - Clear cart
- `GET /api/orders` - Get user's orders
- `GET /api/orders/{id}` - Get order details
- `POST /api/orders` - Create new order

## Sample Data

The seeder creates:
- **Users**: Admin user and sample customers
- **Categories**: Electronics, Clothing, Home & Garden, Sports & Outdoors, Books & Media, Health & Beauty
- **Products**: Sample products in each category with realistic data

### Default Admin Credentials
- Email: `admin@alanwar.com`
- Password: `password123`

### Sample Customer Credentials
- Email: `john.doe@example.com`
- Password: `password123`
- Email: `jane.smith@example.com`
- Password: `password123`

## Database Schema

### Users Table
- `id`, `first_name`, `last_name`, `email`, `phone`
- `address`, `city`, `state`, `postal_code`, `country`
- `role` (user/admin), `password`, `email_verified_at`

### Categories Table
- `id`, `name`, `slug`, `description`, `image`
- `is_active`, `sort_order`

### Products Table
- `id`, `name`, `slug`, `description`, `price`, `sale_price`
- `stock_quantity`, `sku`, `images` (JSON), `specifications` (JSON)
- `is_active`, `is_featured`, `category_id`

### Orders Table
- `id`, `order_number`, `user_id`, `status`, `subtotal`
- `tax`, `shipping_cost`, `total`, `shipping_address`, `billing_address`
- `payment_method`, `payment_status`, `notes`

### Order Items Table
- `id`, `order_id`, `product_id`, `quantity`, `price`, `total`

### Cart Items Table
- `id`, `user_id`, `product_id`, `quantity`

## Testing the API

You can test the API endpoints using tools like:
- **Postman**
- **Insomnia**
- **cURL**
- **Thunder Client** (VS Code extension)

### Example API Calls

1. **Get all products:**
   ```bash
   curl http://localhost:8000/api/products
   ```

2. **Get featured products:**
   ```bash
   curl http://localhost:8000/api/products/featured
   ```

3. **Get products by category:**
   ```bash
   curl http://localhost:8000/api/products/category/1
   ```

4. **User registration:**
   ```bash
   curl -X POST http://localhost:8000/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{
       "first_name": "Test",
       "last_name": "User",
       "email": "test@example.com",
       "password": "password123",
       "password_confirmation": "password123"
     }'
   ```

## Development Notes

- **Authentication**: Currently using temporary user IDs for testing. In production, implement proper Laravel Sanctum authentication.
- **Image Storage**: Product images are stored as file paths. Consider implementing cloud storage (AWS S3, etc.) for production.
- **Payment Processing**: Orders are created with 'pending' payment status. Integrate with payment gateways like Stripe for production.
- **Security**: Implement proper CORS policies and rate limiting for production use.

## Next Steps

1. **Implement Laravel Sanctum** for proper API authentication
2. **Add image upload functionality** for products and categories
3. **Integrate payment gateways** (Stripe, PayPal, etc.)
4. **Add email notifications** for order confirmations
5. **Implement inventory management** with low stock alerts
6. **Add admin panel API endpoints** for managing products, orders, and users
7. **Implement search functionality** with full-text search
8. **Add product reviews and ratings** system
9. **Implement wishlist functionality**
10. **Add discount codes and promotions**

## Support

For any issues or questions, please refer to the Laravel documentation or create an issue in the project repository.
