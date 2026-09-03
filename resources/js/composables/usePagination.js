import { reactive, ref } from 'vue';
import { useDebounceFn } from '@vueuse/core';

export function usePagination(fetcher, { perPage = 20, extraParams = {} } = {}) {
    const items = ref([]);
    const loading = ref(false);
    const errorMessage = ref('');

    const params = reactive({
        page: 1,
        per_page: perPage,
        search: '',
        ...extraParams,
    });

    const meta = reactive({
        currentPage: 1,
        lastPage: 1,
        total: 0,
    });

    async function load() {
        loading.value = true;
        errorMessage.value = '';
        try {
            const response = await fetcher({ ...params });
            items.value = response.data;
            if (response.meta) {
                meta.currentPage = response.meta.current_page;
                meta.lastPage = response.meta.last_page;
                meta.total = response.meta.total;
            }
        } catch (error) {
            errorMessage.value = error?.message ?? 'Could not load the list.';
        } finally {
            loading.value = false;
        }
    }

    const reload = useDebounceFn(() => {
        params.page = 1;
        load();
    }, 350);

    function goToPage(page) {
        params.page = page;
        load();
    }

    function setFilter(key, value) {
        params[key] = value;
        reload();
    }

    return { items, loading, errorMessage, params, meta, load, reload, goToPage, setFilter };
}
