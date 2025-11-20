# E-Commerce Order Management System - Complete Setup Guide

## 🎯 System Overview

A fully-featured Laravel e-commerce backend with:
- JWT Authentication & RBAC
- Product & Inventory Management with variants
- Category Management (hierarchical)
- Complete Order Processing System
- PDF Invoice Generation
- Email Notifications
- Queue-based Jobs
- Comprehensive Testing

---

## 📋 Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+
- Redis (for queues and cache)
- Node.js & NPM (optional, for assets)

---

## 🚀 Installation Steps

### 1. Clone & Install Dependencies

```bash
# Clone repository
git clone <your-repo-url>
cd ecommerce-order-management

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### 2. Configure Database

Edit `.env`:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create database:
```bash
mysql -u root -p
CREATE DATABASE order_management;
exit;
```

### 3. Configure Queue & Cache

```bash
# .env
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Configure Mail

```bash
# For development (Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# For production (Gmail, SendGrid, etc.)
# Update accordingly
```

### 5. Publish Vendor Assets

```bash
# Publish JWT config
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

# Publish Spatie Permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Create storage link
php artisan storage:link
```

### 6. Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Seed database with roles, permissions, and test data
php artisan db:seed

# Or do both at once
php artisan migrate:fresh --seed
```

### 7. Start Services

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# Optional: Watch queue in real-time
php artisan queue:listen --verbose
```

---

## 🔑 Test User Accounts

After seeding, use these credentials:

| Role     | Email                 | Password    | Access Level        |
|----------|-----------------------|-------------|---------------------|
| Admin    | admin@example.com     | password123 | Full system access  |
| Vendor 1 | vendor1@example.com   | password123 | Own products/orders |
| Vendor 2 | vendor2@example.com   | password123 | Own products/orders |
| Customer | customer1@example.com | password123 | Own orders only     |
| Customer | customer2@example.com | password123 | Own orders only     |

---

## 📁 Project Structure

```
ecommerce-order-management/
├── app/
│   ├── Events/              # Order & Inventory events
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/     # API controllers
│   │   ├── Middleware/      # Custom middleware
│   │   └── Requests/        # Form request validators
│   ├── Jobs/                # Queue jobs (emails, etc.)
│   ├── Listeners/           # Event listeners
│   ├── Models/              # Eloquent models
│   ├── Repositories/        # Repository pattern
│   └── Services/            # Business logic
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   └── views/
│       ├── emails/          # Email templates
│       └── invoices/        # Invoice PDF template
├── routes/
│   └── api.php              # API routes
├── storage/
│   └── app/
│       └── public/
│           └── invoices/    # Generated PDFs
└── tests/
    └── Feature/             # Feature tests
```

---

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication Endpoints

```http
POST   /auth/register          # Register new user
POST   /auth/login             # Login
POST   /auth/refresh           # Refresh token
POST   /auth/logout            # Logout
GET    /auth/profile           # Get user profile
```

### Product Endpoints

```http
GET    /products               # List products
POST   /products               # Create product (admin/vendor)
GET    /products/{id}          # Get single product
PUT    /products/{id}          # Update product (admin/vendor)
DELETE /products/{id}          # Delete product (admin/vendor)
GET    /products/search        # Search products
POST   /products/bulk-import   # CSV import (admin/vendor)
PUT    /products/{id}/inventory # Update inventory (admin/vendor)
GET    /products/low-stock     # Low stock products (admin/vendor)
```

### Order Endpoints

```http
GET    /orders                 # List orders
POST   /orders                 # Create order
GET    /orders/{id}            # Get single order
PUT    /orders/{id}/status     # Update status (admin/vendor)
POST   /orders/{id}/cancel     # Cancel order (customer/admin)
GET    /orders/{id}/invoice    # Download invoice
GET    /orders/{id}/status-history # Get status history
GET    /orders/statistics/overview # Statistics (admin)
```

### Example Request (Create Order)

```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 2, "variant_id": 3, "quantity": 1}
    ],
    "shipping_name": "John Doe",
    "shipping_email": "john@example.com",
    "shipping_phone": "+1234567890",
    "shipping_address": "123 Main St",
    "shipping_city": "New York",
    "shipping_country": "USA"
  }'
```

---

## 🔧 Configuration Files

### Important .env Variables

```bash
# App
APP_NAME="E-Commerce Order Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_DATABASE=order_management

# JWT
JWT_SECRET=generated_secret_key
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Queue
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="noreply@example.com"

# Cache
CACHE_DRIVER=redis
```

---

## 🎯 Features Checklist

### ✅ Authentication & Authorization
- [x] JWT authentication
- [x] Refresh tokens
- [x] Role-based access control (Admin, Vendor, Customer)
- [x] Permissions system

### ✅ Product Management
- [x] CRUD operations
- [x] Product variants (size, color, etc.)
- [x] Category management (hierarchical)
- [x] Image storage
- [x] Full-text search
- [x] Bulk CSV import

### ✅ Inventory Management
- [x] Real-time stock tracking
- [x] Inventory transactions log
- [x] Low stock alerts
- [x] Stock status auto-update
- [x] Multi-variant support

### ✅ Order Processing
- [x] Order creation with multiple items
- [x] Status workflow (Pending → Delivered)
- [x] Automatic inventory deduction
- [x] Order cancellation with stock restoration
- [x] PDF invoice generation
- [x] Email notifications
- [x] Order history tracking

### ✅ Technical Requirements
- [x] Repository pattern
- [x] Service layer architecture
- [x] Queue-based jobs
- [x] Event-driven system
- [x] Database transactions
- [x] Comprehensive validation
- [x] Feature & unit tests
- [x] API versioning (v1)

---

# Low Stock Alerts System

## 🚨 Automatic Detection
The system automatically:

- ✅ Detects when stock falls below threshold
- ✅ Creates a `LowStockAlert` record
- ✅ Dispatches a queued job
- ✅ Sends email notifications to vendor/admin

## ⚙️ Configuration
Set threshold per product:

```json
{
  "low_stock_threshold": 10
}

## 🐛 Troubleshooting

### Issue: JWT Token Invalid
```bash
php artisan jwt:secret
php artisan config:clear
php artisan cache:clear
```

### Issue: Queue Jobs Not Processing
```bash
# Check Redis connection
redis-cli ping

# Restart queue worker
php artisan queue:restart
php artisan queue:work
```

### Issue: Storage Link Broken
```bash
php artisan storage:link
```

### Issue: Permission Denied on Storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Emails Not Sending
```bash
# Check mail configuration
php artisan config:clear

# Test mail in tinker
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('test@example.com'); });
```

---

## 📊 Performance Optimization

### Database Indexing
All critical fields are indexed:
- Product: SKU, category_id, vendor_id
- Order: order_number, user_id, status
- Full-text: product name, description

### Query Optimization
- Eager loading relationships
- N+1 query prevention
- Pagination on all list endpoints

### Caching Strategy
```php
// Cache categories tree (rarely changes)
Cache::remember('categories_tree', 3600, function() {
    return Category::tree()->get();
});

// Cache product counts
Cache::remember('product_stats', 600, function() {
    return Product::statistics();
});
```

### Queue Configuration
```bash
# Use multiple workers in production
php artisan queue:work --queue=high,default,low --tries=3
```

---

## 🚀 Deployment

### Production Checklist

1. **Environment Configuration**
```bash
APP_ENV=production
APP_DEBUG=false
```

2. **Optimize Application**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Database**
```bash
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
```

4. **Queue Worker (Supervisor)**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

5. **Cron Job**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📖 Additional Documentation

- [Authentication Guide](./AUTHENTICATION_SETUP.md)
- [Product & Inventory Guide](./PRODUCT_INVENTORY_GUIDE.md)
- [Category API Guide](./CATEGORY_API_GUIDE.md)
- [Order Processing Guide](./ORDER_PROCESSING_GUIDE.md)
- [Request Validation Guide](./REQUEST_VALIDATION_GUIDE.md)

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📝 License

This project is licensed under the MIT License.

---

## 🎉 Success!

Your E-Commerce Order Management System is now fully set up and ready to use!

**Start the application:**
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:work

# Visit: http://localhost:8000
```

**Test the API:**
```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}'
```

Happy coding! 🚀
