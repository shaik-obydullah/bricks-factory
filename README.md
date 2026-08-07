# Bricks Factory Management System

A complete production management system for brick manufacturing factories in Bangladesh. Built with **Laravel 13**, **Vue 3**, **MySQL**, **phpMyAdmin**, **nginx** and **Docker**.

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3" />
  <img src="https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=composer&logoColor=white" alt="Composer" />
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Vite_8-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 8" />
  <img src="https://img.shields.io/badge/Node_20-5FA04E?style=for-the-badge&logo=node.js&logoColor=white" alt="Node 20" />
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4" />
  <img src="https://img.shields.io/badge/Pinia-FFD859?style=for-the-badge&logo=pinia&logoColor=black" alt="Pinia" />
  <img src="https://img.shields.io/badge/MySQL_8-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8" />
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/nginx-009639?style=for-the-badge&logo=nginx&logoColor=white" alt="Nginx" />
  <img src="https://img.shields.io/badge/phpMyAdmin-6C78AF?style=for-the-badge&logo=phpmyadmin&logoColor=white" alt="phpMyAdmin" />
  <img src="https://img.shields.io/badge/Axios-5A29E4?style=for-the-badge&logo=axios&logoColor=white" alt="Axios" />
  <img src="https://img.shields.io/badge/Sanctum-2E3A59?style=for-the-badge" alt="Laravel Sanctum" />
  <img src="https://img.shields.io/badge/JavaScript-ES2022-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />
  <img src="https://img.shields.io/badge/License-MIT-10B981?style=for-the-badge" alt="MIT License" />
</p>

## Features

### Production Management
- Production orders with status tracking (draft, confirmed, in_progress, completed, cancelled)
- Shift management
- Machine tracking with maintenance scheduling
- Production batches and targets
- Real-time output monitoring

### Inventory Management
- Products & categories
- Raw materials (clay, sand, water, etc.)
- Stock movements (in / out / transfer)
- Low stock alerts
- Warehouse management

### Quality Control
- Quality checks per batch
- Check items (size, strength, color)
- Defect tracking with severity levels
- Pass / fail workflow

### Sales & Orders
- Customer management
- Sales orders with line items
- Invoice generation
- Payment tracking

### Purchase Management
- Supplier management
- Purchase orders
- Goods receipt

### Employees & Attendance
- Employee registry
- Attendance tracking with shift assignments

### Reports & Analytics
- Production, inventory, sales, quality reports
- Dashboard with key metrics

## Screenshots

### Auth & Dashboard
<p>
  <img src="screenshots/Admin_Login.png" width="400" alt="Admin Login" />
  <img src="screenshots/Admin_Dashboard.png" width="400" alt="Admin Dashboard" />
</p>

### Production
<p>
  <img src="screenshots/Production_Orders.png" width="400" alt="Production Orders" />
  <img src="screenshots/Create_Production_Order.png" width="400" alt="Create Production Order" />
  <img src="screenshots/Production-Batches.png" width="400" alt="Production Batches" />
  <img src="screenshots/Production-Order-Detail.png" width="400" alt="Production Order Detail" />
  <img src="screenshots/Production-Targets.png" width="400" alt="Production Targets" />
  <img src="screenshots/Machines.png" width="400" alt="Machines" />
  <img src="screenshots/Add-Machine.png" width="400" alt="Add Machine" />
</p>

### Inventory
<p>
  <img src="screenshots/Products.png" width="400" alt="Products" />
  <img src="screenshots/Create-Product.png" width="400" alt="Create Product" />
  <img src="screenshots/Raw-Materials.png" width="400" alt="Raw Materials" />
  <img src="screenshots/Categories.png" width="400" alt="Categories" />
  <img src="screenshots/Warehouses.png" width="400" alt="Warehouses" />
  <img src="screenshots/Stock-Movements.png" width="400" alt="Stock Movements" />
  <img src="screenshots/Stock-Alerts.png" width="400" alt="Stock Alerts" />
</p>

### Quality Control
<p>
  <img src="screenshots/Quality-Checks.png" width="400" alt="Quality Checks" />
  <img src="screenshots/Defects.png" width="400" alt="Defects" />
</p>

### Sales & Purchase
<p>
  <img src="screenshots/Customers.png" width="400" alt="Customers" />
  <img src="screenshots/Sales-Orders.png" width="400" alt="Sales Orders" />
  <img src="screenshots/New-Sales-Order.png" width="400" alt="New Sales Order" />
  <img src="screenshots/Invoices.png" width="400" alt="Invoices" />
  <img src="screenshots/Payments.png" width="400" alt="Payments" />
  <img src="screenshots/Record-Payment.png" width="400" alt="Record Payment" />
  <img src="screenshots/Suppliers.png" width="400" alt="Suppliers" />
  <img src="screenshots/Purchase-Orders.png" width="400" alt="Purchase Orders" />
  <img src="screenshots/New-Purchase-Order.png" width="400" alt="New Purchase Order" />
  <img src="screenshots/Goods-Receipts.png" width="400" alt="Goods Receipts" />
</p>

### Employees, Settings & Reports
<p>
  <img src="screenshots/Employees.png" width="400" alt="Employees" />
  <img src="screenshots/Add-Employee.png" width="400" alt="Add Employee" />
  <img src="screenshots/Attendance.png" width="400" alt="Attendance" />
  <img src="screenshots/APP-Settings.png" width="400" alt="App Settings" />
  <img src="screenshots/Roles-and-Permissions.png" width="400" alt="Roles and Permissions" />
  <img src="screenshots/Add-Role.png" width="400" alt="Add Role" />
  <img src="screenshots/Production-Report.png" width="400" alt="Production Report" />
  <img src="screenshots/Inventory-Report.png" width="400" alt="Inventory Report" />
  <img src="screenshots/Sales-Report.png" width="400" alt="Sales Report" />
  <img src="screenshots/Quality-Report.png" width="400" alt="Quality Report" />
</p>

## Tech Stack

| Layer       | Technology                          |
|-------------|-------------------------------------|
| Backend     | Laravel, PHP 8.3                    |
| Frontend    | Vue 3, Vue Router, Pinia, Tailwind CSS 4 |
| Database    | MySQL 8.0                            |
| Auth        | Laravel Sanctum + Spatie Permissions |
| Queue/Cache | Database driver (no extra services) |
| Web Server  | Nginx                                |
| Container   | Docker + Docker Compose              |
| Database UI | phpMyAdmin                           |

## Docker Services & Ports

| Service      | Port | URL                     |
|--------------|------|-------------------------|
| App (nginx)  | 8092 | http://localhost:8092   |
| phpMyAdmin   | 8093 | http://localhost:8093   |
| MySQL        | 8094 | localhost:8094          |

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/shaik-obydullah/bricks-factory.git
cd bricks-factory

# 2. Build & start containers
docker compose up -d --build

# 3. Install PHP dependencies
docker compose exec -u www app composer install

# 4. Configure environment
cp .env.example .env
docker compose exec -u www app php artisan key:generate

# 5. Run migrations & seeders
docker compose exec -u www app php artisan migrate --seed

# 6. Build frontend assets
npm install
npm run build

# 7. Open the app
# http://localhost:8092
```

## Default Login

| Role  | Email            | Password |
|-------|------------------|----------|
| Admin | admin@bricks.com | password |

## API Endpoints

### Auth
```
POST   /api/login
POST   /api/logout
GET    /api/user
```

### Production
```
GET/POST   /api/production/orders
GET/PUT/DELETE /api/production/orders/{id}
GET        /api/production/orders/{id}/batches
POST       /api/production/batches/start
PUT        /api/production/batches/{id}/complete
GET/POST   /api/machines
GET/POST   /api/production/targets
```

### Inventory
```
GET/POST          /api/products
GET/PUT/DELETE    /api/products/{id}
GET/POST          /api/raw-materials
GET/POST          /api/stock-movements
GET               /api/stock-alerts
GET/POST/PUT/DELETE /api/categories
GET/POST/PUT/DELETE /api/warehouses
GET/POST/PUT/DELETE /api/units
```

### Quality
```
GET/POST   /api/quality/checks
GET        /api/quality/checks/{id}
PUT        /api/quality/check-items/{id}
GET/POST   /api/quality/defects
PUT        /api/quality/defects/{id}/resolve
```

### Sales
```
GET/POST            /api/customers
GET/PUT/DELETE      /api/customers/{id}
GET/POST            /api/sales/orders
GET                 /api/sales/orders/{id}
GET                 /api/sales/invoices
POST                /api/sales/orders/{id}/invoice
POST                /api/sales/invoices/{id}/pay
```

### Purchase
```
GET/POST            /api/suppliers
GET/PUT/DELETE      /api/suppliers/{id}
GET/POST            /api/purchase/orders
GET                 /api/purchase/orders/{id}
POST                /api/purchase/orders/{id}/receive
```

### Employees
```
GET/POST            /api/employees
GET/PUT/DELETE      /api/employees/{id}
GET/POST            /api/attendance
```

### Reports
```
GET   /api/reports/dashboard
GET   /api/reports/production
GET   /api/reports/inventory
GET   /api/reports/sales
GET   /api/reports/quality
GET   /api/reports/purchase
```

## Common Commands

```bash
# View container status
docker compose ps

# Tail app logs
docker compose logs -f app

# Run artisan commands
docker compose exec -u www app php artisan <command>

# Rebuild frontend after changes
npm run dev        # development with HMR
npm run build      # production build

# Reset database with seed data
docker compose exec -u www app php artisan migrate:fresh --seed
```

## License

MIT
