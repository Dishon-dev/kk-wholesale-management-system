import { createRouter, createWebHistory } from 'vue-router';
import { authGuard } from './guard';
import { PERMISSIONS } from '@/utils/constants';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/auth/Login.vue'),
        meta: {
            public: true,
        },
    },

    {
        path: '/',
        component: () => import('@/components/layout/AppShell.vue'),

        children: [
            {
                path: '',
                redirect: { name: 'dashboard' },
            },

            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('@/pages/dashboard/Dashboard.vue'),
                meta: {
                    permission: PERMISSIONS.DASHBOARD_VIEW,
                },
            },

            {
                path: 'sales',
                name: 'sales.index',
                component: () => import('@/pages/sales/SalesList.vue'),
                meta: {
                    permission: PERMISSIONS.SALES_VIEW,
                },
            },

            {
                path: 'sales/new',
                name: 'sales.pos',
                component: () => import('@/pages/sales/PointOfSale.vue'),
                meta: {
                    permission: PERMISSIONS.SALES_CREATE,
                },
            },

            {
                path: 'sales/:id',
                name: 'sales.show',
                component: () => import('@/pages/sales/SaleDetail.vue'),
                meta: {
                    permission: PERMISSIONS.SALES_VIEW,
                },
                props: true,
            },

            {
                path: 'stock',
                name: 'stock.index',
                component: () => import('@/pages/stock/StockOverview.vue'),
                meta: {
                    permission: PERMISSIONS.INVENTORY_VIEW,
                },
            },

            {
                path: 'stock/movements',
                name: 'stock.movements',
                component: () => import('@/pages/stock/StockMovements.vue'),
                meta: {
                    permission: PERMISSIONS.STOCK_MOVEMENTS_VIEW,
                },
            },

            {
                path: 'stock/adjustments',
                name: 'stock.adjustments',
                component: () => import('@/pages/stock/StockAdjustments.vue'),
                meta: {
                    permission: PERMISSIONS.INVENTORY_ADJUST,
                },
            },

            {
                path: 'transfers',
                name: 'transfers.index',
                component: () => import('@/pages/transfers/TransfersList.vue'),
                meta: {
                    permission: PERMISSIONS.INVENTORY_TRANSFER,
                },
            },

            {
                path: 'transfers/new',
                name: 'transfers.create',
                component: () => import('@/pages/transfers/TransferForm.vue'),
                meta: {
                    permission: PERMISSIONS.INVENTORY_TRANSFER,
                },
            },

            {
                path: 'transfers/:id',
                name: 'transfers.show',
                component: () => import('@/pages/transfers/TransferDetail.vue'),
                meta: {
                    permission: PERMISSIONS.INVENTORY_TRANSFER,
                },
                props: true,
            },

            {
                path: 'products',
                name: 'products.index',
                component: () => import('@/pages/products/ProductsList.vue'),
                meta: {
                    permission: PERMISSIONS.PRODUCTS_VIEW,
                },
            },

            {
                path: 'products/new',
                name: 'products.create',
                component: () => import('@/pages/products/ProductForm.vue'),
                meta: {
                    permission: PERMISSIONS.PRODUCTS_CREATE,
                },
            },

            {
                path: 'products/:id/edit',
                name: 'products.edit',
                component: () => import('@/pages/products/ProductForm.vue'),
                meta: {
                    permission: PERMISSIONS.PRODUCTS_UPDATE,
                },
                props: true,
            },

            {
                path: 'products/:id',
                name: 'products.show',
                component: () => import('@/pages/products/ProductDetail.vue'),
                meta: {
                    permission: PERMISSIONS.PRODUCTS_VIEW,
                },
                props: true,
            },

            {
                path: 'categories',
                name: 'categories.index',
                component: () => import('@/pages/categories/CategoriesList.vue'),
                meta: {
                    permission: PERMISSIONS.CATEGORIES_VIEW,
                },
            },

            {
                path: 'branches',
                name: 'branches.index',
                component: () => import('@/pages/branches/BranchesList.vue'),
                meta: {
                    permission: PERMISSIONS.BRANCHES_VIEW,
                },
            },

            {
                path: 'stores',
                name: 'stores.index',
                component: () => import('@/pages/stores/StoresList.vue'),
                meta: {
                    permission: PERMISSIONS.STORES_VIEW,
                },
            },

            {
                path: 'users',
                name: 'users.index',
                component: () => import('@/pages/users/UsersList.vue'),
                meta: {
                    permission: PERMISSIONS.USERS_VIEW,
                },
            },

            {
                path: 'roles',
                name: 'roles.index',
                component: () => import('@/pages/roles/RolesList.vue'),
                meta: {
                    permission: PERMISSIONS.ROLES_VIEW,
                },
            },

            {
                path: 'activity',
                name: 'activity.index',
                component: () => import('@/pages/activity/ActivityLogs.vue'),
                meta: {
                    permission: PERMISSIONS.ACTIVITY_VIEW,
                },
            },

            {
                path: 'alerts',
                name: 'alerts.index',
                component: () => import('@/pages/alerts/StockAlerts.vue'),
                meta: {
                    permission: PERMISSIONS.ALERTS_VIEW,
                },
            },

            {
                path: 'account/profile',
                name: 'account.profile',
                component: () => import('@/pages/account/Profile.vue'),
            },

            {
                path: '403',
                name: 'forbidden',
                component: () => import('@/pages/errors/Forbidden.vue'),
            },
        ],
    },

    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/errors/NotFound.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

router.beforeEach(authGuard);

export default router;
