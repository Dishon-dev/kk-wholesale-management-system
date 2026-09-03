/**
 * Stock movement types, mirrored from the `stock_movements.movement_type`
 * enum on the backend. `sign` is used purely for client-side display (e.g.
 * colouring a +/- prefix) — the authoritative balance math happens server
 * side inside the transaction that writes the row.
 */
export const MOVEMENT_TYPES = {
    INITIAL_STOCK: { label: 'Initial stock', tone: 'neutral', sign: '+' },
    SALE: { label: 'Sale', tone: 'negative', sign: '-' },
    SALE_RETURN: { label: 'Sale return', tone: 'positive', sign: '+' },
    PURCHASE: { label: 'Purchase', tone: 'positive', sign: '+' },
    PURCHASE_RETURN: { label: 'Purchase return', tone: 'negative', sign: '-' },
    TRANSFER_IN: { label: 'Transfer in', tone: 'positive', sign: '+' },
    TRANSFER_OUT: { label: 'Transfer out', tone: 'negative', sign: '-' },
    ADJUSTMENT: { label: 'Adjustment', tone: 'info', sign: '±' },
    DAMAGE: { label: 'Damage', tone: 'negative', sign: '-' },
    LOST: { label: 'Lost', tone: 'negative', sign: '-' },
    FOUND: { label: 'Found', tone: 'positive', sign: '+' },
    EXPIRED: { label: 'Expired', tone: 'negative', sign: '-' },
};

export const TRANSFER_STATUSES = {
    PENDING: { label: 'Pending', tone: 'neutral' },
    APPROVED: { label: 'Approved', tone: 'info' },
    IN_TRANSIT: { label: 'In transit', tone: 'warning' },
    RECEIVED: { label: 'Received', tone: 'positive' },
    PARTIALLY_RECEIVED: { label: 'Partially received', tone: 'warning' },
    REJECTED: { label: 'Rejected', tone: 'negative' },
    CANCELLED: { label: 'Cancelled', tone: 'negative' },
};

export const SALE_STATUSES = {
    COMPLETED: { label: 'Completed', tone: 'positive' },
    VOIDED: { label: 'Voided', tone: 'negative' },
    REFUNDED: { label: 'Refunded', tone: 'warning' },
};

export const ADJUSTMENT_TYPES = {
    INCREASE: { label: 'Increase', tone: 'positive' },
    DECREASE: { label: 'Decrease', tone: 'negative' },
};

export const ADJUSTMENT_STATUSES = {
    PENDING: { label: 'Pending approval', tone: 'neutral' },
    APPROVED: { label: 'Approved', tone: 'positive' },
    REJECTED: { label: 'Rejected', tone: 'negative' },
};

export const ALERT_TYPES = {
    LOW_STOCK: { label: 'Low stock', tone: 'warning' },
    OUT_OF_STOCK: { label: 'Out of stock', tone: 'negative' },
};

/**
 * Permission keys the UI checks against. These are suggestions for the
 * backend seeder (see docs/API-CONTRACT.md) — the frontend never hardcodes
 * role names, it only ever checks permission strings returned at login, so
 * new roles/permissions created later work without a frontend change.
 */

export const PERMISSIONS = {
    // Dashboard / Reports
    DASHBOARD_VIEW: 'reports.view',
    REPORTS_VIEW: 'reports.view',

    // Roles
    ROLES_VIEW: 'roles.view',
    ROLES_CREATE: 'roles.create',
    ROLES_UPDATE: 'roles.update',
    ROLES_ASSIGN: 'roles.assign',
    ROLES_DELETE: 'roles.delete',

    // Users
    USERS_VIEW: 'users.view',
    USERS_CREATE: 'users.create',
    USERS_UPDATE: 'users.update',
    USERS_DELETE: 'users.delete',

    // Branches
    BRANCHES_VIEW: 'branches.view',
    BRANCHES_CREATE: 'branches.create',
    BRANCHES_UPDATE: 'branches.update',
    BRANCHES_DELETE: 'branches.delete',

    // Stores
    STORES_VIEW: 'stores.view',
    STORES_CREATE: 'stores.create',
    STORES_UPDATE: 'stores.update',
    STORES_DELETE: 'stores.delete',

    // Categories
    CATEGORIES_VIEW: 'categories.view',
    CATEGORIES_CREATE: 'categories.create',
    CATEGORIES_UPDATE: 'categories.update',
    CATEGORIES_DELETE: 'categories.delete',

    // Brands
    BRANDS_VIEW: 'brands.view',
    BRANDS_CREATE: 'brands.create',
    BRANDS_UPDATE: 'brands.update',
    BRANDS_DELETE: 'brands.delete',

    // Products
    PRODUCTS_VIEW: 'products.view',
    PRODUCTS_CREATE: 'products.create',
    PRODUCTS_UPDATE: 'products.update',
    PRODUCTS_DELETE: 'products.delete',

    // Product Variants
    PRODUCT_VARIANTS_VIEW: 'product_variants.view',
    PRODUCT_VARIANTS_CREATE: 'product_variants.create',
    PRODUCT_VARIANTS_UPDATE: 'product_variants.update',
    PRODUCT_VARIANTS_DELETE: 'product_variants.delete',

    // Product Options
    PRODUCT_OPTIONS_VIEW: 'product_options.view',
    PRODUCT_OPTIONS_CREATE: 'product_options.create',
    PRODUCT_OPTIONS_UPDATE: 'product_options.update',
    PRODUCT_OPTIONS_DELETE: 'product_options.delete',

    // Product Option Values
    PRODUCT_OPTION_VALUES_VIEW: 'product_option_values.view',
    PRODUCT_OPTION_VALUES_CREATE: 'product_option_values.create',
    PRODUCT_OPTION_VALUES_UPDATE: 'product_option_values.update',
    PRODUCT_OPTION_VALUES_DELETE: 'product_option_values.delete',

    // Inventory
    INVENTORY_VIEW: 'inventory.view',
    INVENTORY_ADJUST: 'inventory.adjust',
    INVENTORY_TRANSFER: 'inventory.transfer',

    // Stock Movements
    STOCK_MOVEMENTS_VIEW: 'stock_movements.view',

    // Sales
    SALES_VIEW: 'sales.view',
    SALES_CREATE: 'sales.create',
    SALES_VOID: 'sales.void',

    // Payments
    PAYMENTS_VIEW: 'payments.view',
    PAYMENTS_CREATE: 'payments.create',

    // Returns
    RETURNS_VIEW: 'returns.view',
    RETURNS_CREATE: 'returns.create',
    RETURNS_CANCEL: 'returns.cancel',
};