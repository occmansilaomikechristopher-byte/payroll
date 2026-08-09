# POS System Setup Guide

## Quick Setup Instructions

### Step 1: Create Database Tables

Copy and paste the following SQL commands into your XAMPP phpMyAdmin SQL console:

```sql
-- 1. Create Branch Table
CREATE TABLE IF NOT EXISTS `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_code` varchar(50) NOT NULL UNIQUE,
  `branch_name` varchar(150) NOT NULL,
  `address` text,
  `city` varchar(100),
  `phone` varchar(20),
  `email` varchar(100),
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_code` (`branch_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create Product Category Table
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_code` varchar(50) NOT NULL UNIQUE,
  `category_name` varchar(150) NOT NULL,
  `description` text,
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL UNIQUE,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `description` text,
  `quantity_on_hand` decimal(10,2) DEFAULT 0,
  `reorder_level` decimal(10,2) DEFAULT 10,
  `unit_price` decimal(12,2) NOT NULL,
  `cost_price` decimal(12,2),
  `unit` varchar(20) COMMENT 'pcs, kg, liter, etc.',
  `status` tinyint(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `category_id` (`category_id`),
  KEY `branch_id` (`branch_id`),
  FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create Audit Log for POS Changes (to track updates since no delete)
CREATE TABLE IF NOT EXISTS `pos_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100),
  `record_id` int(11),
  `action` varchar(50) COMMENT 'INSERT, UPDATE, INACTIVATE',
  `old_data` json,
  `new_data` json,
  `user_id` int(11),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `table_name` (`table_name`),
  KEY `record_id` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2: Sample Data (Optional)

If you want to add sample branches and categories:

```sql
INSERT INTO branches (branch_code, branch_name, city, phone, email, status) VALUES
('BR001', 'Main Branch', 'Manila', '(02) 1234-5678', 'main@company.com', 1),
('BR002', 'Makati Branch', 'Makati', '(02) 8765-4321', 'makati@company.com', 1);

INSERT INTO product_categories (category_code, category_name, description, status) VALUES
('CAT001', 'Beverages', 'All types of drinks', 1),
('CAT002', 'Food Items', 'Ready to eat and packaged foods', 1),
('CAT003', 'Electronics', 'Electronic devices and accessories', 1);
```

### Step 3: Access the POS System

After running the SQL commands, you can access the POS system:

- **URL**: `http://localhost/payroll/index.php?page=pos`
- **Dashboard**: Shows summary of branches, categories, products, and inventory
- **Menu**: Look for "POS System" in the left navigation menu

---

## Features

### 1. **Branches Management**
- Add new branches with code, name, city, phone, and email
- Edit branch information
- Activate/Deactivate branches
- **No Delete** — branches are inactivated instead

### 2. **Product Categories**
- Create product categories (e.g., Beverages, Food, Electronics)
- Edit category names and descriptions
- Manage active/inactive status
- **No Delete** — categories are inactivated

### 3. **Products Inventory**
- Add products with:
  - Product code (unique identifier)
  - Product name
  - Category assignment
  - Branch assignment
  - Unit price and cost price
  - Quantity on hand
  - Reorder level (for low stock alerts)
  - Unit type (pcs, kg, liter, etc.)
- Edit product details
- Track inventory levels
- **No Delete** — products are inactivated

### 4. **Dashboard**
- Quick stats for:
  - Total active branches
  - Total categories
  - Total active products
  - Total inventory quantity
- Quick action buttons to add new items

---

## Database Fields Explanation

### Branches Table
| Field | Type | Description |
|-------|------|-------------|
| `branch_code` | varchar(50) | Unique identifier for branch (e.g., BR001) |
| `branch_name` | varchar(150) | Full name of the branch |
| `city` | varchar(100) | City location |
| `phone` | varchar(20) | Contact phone number |
| `email` | varchar(100) | Email address |
| `status` | tinyint | 1 = Active, 0 = Inactive |

### Product Categories Table
| Field | Type | Description |
|-------|------|-------------|
| `category_code` | varchar(50) | Unique code (e.g., CAT001) |
| `category_name` | varchar(150) | Category display name |
| `description` | text | Additional details about category |
| `status` | tinyint | 1 = Active, 0 = Inactive |

### Products Table
| Field | Type | Description |
|-------|------|-------------|
| `product_code` | varchar(50) | Unique product identifier |
| `product_name` | varchar(150) | Product display name |
| `category_id` | int | Foreign key to product_categories |
| `branch_id` | int | Foreign key to branches |
| `quantity_on_hand` | decimal(10,2) | Current stock level |
| `reorder_level` | decimal(10,2) | Minimum quantity before reorder |
| `unit_price` | decimal(12,2) | Selling price |
| `cost_price` | decimal(12,2) | Purchase cost |
| `unit` | varchar(20) | Unit of measurement (pcs, kg, etc.) |
| `status` | tinyint | 1 = Active, 0 = Inactive |

### Audit Log Table
Tracks all changes to branches, categories, and products (for compliance and history)

---

## CRUD Operations

### CREATE
- Use the "Add" buttons to create new branches, categories, or products
- All required fields are marked with a red asterisk (*)
- Codes must be unique

### READ
- All items are displayed in tables
- Tables show essential information at a glance
- Filter by status (Active/Inactive)

### UPDATE
- Click the "Edit" button on any row
- Modify the details in the modal
- Click "Update" to save changes
- **Important**: Codes cannot be changed after creation

### DELETE
- **NO DELETE FUNCTION** — Items are inactivated instead
- Set status to "Inactive" to hide from active lists
- Historical data is preserved in the audit log

---

## Color Scheme

The POS system uses the brand primary color:
- **Primary Blue**: `#219688` (teal)
- **Light Background**: `#e6f5f3`
- **Dark Accent**: `#176358`

All UI elements match the application's overall design theme.

---

## Files Created

1. **pos.php** — Main POS dashboard and routing
2. **component/pos_branches.php** — Branches management table
3. **component/pos_categories.php** — Categories management table
4. **component/pos_products.php** — Products management table
5. **component/pos_modals.php** — All add/edit modals and AJAX handlers
6. **admin_class.php** — Added CRUD methods for POS operations
7. **includes/navbar.php** — Updated with POS menu items

---

## How to Use

### Adding a Branch
1. Click "POS System" → "Branches" in the menu
2. Click the "Add Branch" button
3. Fill in the required fields
4. Click "Save Branch"

### Adding a Product Category
1. Click "POS System" → "Categories" in the menu
2. Click the "Add Category" button
3. Enter category code and name
4. Click "Save Category"

### Adding a Product
1. Click "POS System" → "Products" in the menu
2. Click the "Add Product" button
3. Fill in all required fields:
   - Product code (unique)
   - Product name
   - Select category
   - Select branch
   - Enter unit price
   - Set quantity and reorder level
4. Click "Save Product"

### Editing Items
1. Find the item in the table
2. Click the "Edit" button
3. Modify the details
4. Click "Update"

### Deactivating Items
1. Open the edit modal
2. Change status from "Active" to "Inactive"
3. Click "Update"
4. Item will still exist in database but won't appear in active lists

---

## Troubleshooting

**Issue**: "Branch code already exists"
- **Solution**: Use a unique branch code (e.g., BR003 instead of BR001)

**Issue**: Cannot delete a category with products
- **Solution**: This is by design (data integrity). Inactivate the category instead.

**Issue**: Low stock warnings
- **Solution**: Yellow badge appears when quantity ≤ reorder level. Update reorder level in product settings.

---

## Support

For additional features or modifications, contact your development team.

Version: 1.0
Last Updated: 2026-06-21
