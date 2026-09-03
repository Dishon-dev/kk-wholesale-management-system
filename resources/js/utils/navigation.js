import { PERMISSIONS } from '@/utils/constants';

export const NAV_GROUPS = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard', route: 'dashboard', permission: PERMISSIONS.DASHBOARD_VIEW, icon: 'grid' },
        ],
    },
    {
        label: 'Shop',
        items: [
            { label: 'Branches', route: 'branches.index', permission: PERMISSIONS.BRANCHES_VIEW, icon: 'building' },
            { label: 'Stores', route: 'stores.index', permission: PERMISSIONS.STORES_VIEW, icon: 'store' },
        ],
    },
    {
        label: 'Catalog',
        items: [
            { label: 'Categories', route: 'categories.index', permission: PERMISSIONS.CATEGORIES_MANAGE, icon: 'folder' },
            { label: 'Products', route: 'products.index', permission: PERMISSIONS.PRODUCTS_VIEW, icon: 'tag' },
        ],
    },
    {
        label: 'Sales',
        items: [
            { label: 'New sale', route: 'sales.pos', permission: PERMISSIONS.SALES_CREATE, icon: 'cart' },
            { label: 'Sales history', route: 'sales.index', permission: PERMISSIONS.SALES_VIEW, icon: 'receipt' },
        ],
    },
    {
        label: 'Inventory',
        items: [
            { label: 'Stock by store', route: 'stock.index', permission: PERMISSIONS.STOCK_VIEW, icon: 'boxes' },
            { label: 'Stock movements', route: 'stock.movements', permission: PERMISSIONS.STOCK_VIEW, icon: 'history' },
            { label: 'Adjustments', route: 'stock.adjustments', permission: PERMISSIONS.STOCK_ADJUST, icon: 'sliders' },
            { label: 'Transfers', route: 'transfers.index', permission: PERMISSIONS.TRANSFERS_VIEW, icon: 'swap' },
            { label: 'Stock alerts', route: 'alerts.index', permission: PERMISSIONS.ALERTS_VIEW, icon: 'bell' },
        ],
    },
    {
        label: 'Administration',
        items: [
            { label: 'Users', route: 'users.index', permission: PERMISSIONS.USERS_MANAGE, icon: 'users' },
            { label: 'Roles & permissions', route: 'roles.index', permission: PERMISSIONS.ROLES_MANAGE, icon: 'shield' },
            { label: 'Activity log', route: 'activity.index', permission: PERMISSIONS.ACTIVITY_VIEW, icon: 'clock' },
        ],
    },
];
