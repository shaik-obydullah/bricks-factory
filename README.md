# Bricks Factory Management System

A complete production management system for brick manufacturing factories in Bangladesh. Built with **Laravel 13**, **Vue 3**, **MySQL**, **phpMyAdmin**, **nginx** and **Docker**.

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3" />
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/Vue_3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Tailwind_CSS_4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4" />
  <img src="https://img.shields.io/badge/Vite_8-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 8" />
  <img src="https://img.shields.io/badge/MySQL_8-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8" />
  <img src="https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis" />
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/Pinia-FFD859?style=for-the-badge&logo=pinia&logoColor=black" alt="Pinia" />
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

## Tech Stack

| Layer       | Technology                          |
|-------------|-------------------------------------|
| Backend     | Laravel, PHP 8.3                    |
| Frontend    | Vue 3, Vue Router, Pinia, Tailwind CSS 4 |
| Database    | MySQL 8.0                            |
| Auth        | Laravel Sanctum + Spatie Permissions |
| Queue/Cache | Redis                                |
| Web Server  | Nginx                                |
| Container   | Docker + Docker Compose              |
| Database UI | phpMyAdmin                           |

## Docker Services & Ports

| Service     | Port | URL                     |
|-------------|------|-------------------------|
| App (nginx) | 8015 | http://localhost:8015   |
| MySQL       | 8016 | localhost:8016          |
| phpMyAdmin  | 8080 | http://localhost:8080   |
| Redis       | 6379 | localhost:6379          |

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
# http://localhost:8015
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
