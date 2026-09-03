<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import * as transfersService from '@/services/transfers.service';
import { formatDateTime, formatNumber } from '@/utils/formatters';
import { TRANSFER_STATUSES } from '@/utils/constants';
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue';
import StatusTag from '@/components/ui/StatusTag.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const { can } = usePermissions();
const toast = useToast();
const confirm = useConfirm();

const loading = ref(true);
const transfer = ref(null);
const mode = ref('view'); // view | sending | receiving
const workingItems = ref([]);

async function load() {
    loading.value = true;
    try {
        const { data } = await transfersService.get(props.id);
        transfer.value = data;
        mode.value = 'view';
    } finally {
        loading.value = false;
    }
}

onMounted(load);

const canApprove = computed(() => transfer.value?.status === 'PENDING' && can('transfers.approve'));
const canCancel = computed(() => ['PENDING', 'APPROVED'].includes(transfer.value?.status) && can('transfers.approve'));
const canSend = computed(() => transfer.value?.status === 'APPROVED' && can('transfers.send'));
const canReceive = computed(() => ['IN_TRANSIT', 'PARTIALLY_RECEIVED'].includes(transfer.value?.status) && can('transfers.receive'));

async function handleApprove() {
    const ok = await confirm({ title: 'Approve this transfer?', message: 'The source store can then dispatch the items.', confirmLabel: 'Approve' });
    if (!ok) return;
    try {
        await transfersService.approve(transfer.value.id);
        toast.success('Transfer approved.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}

async function handleCancel() {
    const ok = await confirm({ title: 'Cancel this transfer?', message: 'This cannot be undone.', confirmLabel: 'Cancel transfer' });
    if (!ok) return;
    try {
        await transfersService.cancel(transfer.value.id, {});
        toast.success('Transfer cancelled.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}

function startSending() {
    workingItems.value = transfer.value.items.map((item) => ({
        product_variant_id: item.product_variant_id,
        quantity_sent: item.quantity_requested,
    }));
    mode.value = 'sending';
}

function startReceiving() {
    workingItems.value = transfer.value.items.map((item) => ({
        product_variant_id: item.product_variant_id,
        quantity_received: item.quantity_sent ?? item.quantity_requested,
    }));
    mode.value = 'receiving';
}

async function confirmSend() {
    try {
        await transfersService.send(transfer.value.id, { items: workingItems.value });
        toast.success('Transfer marked as sent.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}

async function confirmReceive() {
    try {
        await transfersService.receive(transfer.value.id, { items: workingItems.value });
        toast.success('Transfer received.');
        load();
    } catch (error) {
        toast.error(error.message);
    }
}
</script>

<template>
    <LoadingSpinner v-if="loading" label="Loading transfer" />

    <div v-else-if="transfer" class="mx-auto max-w-3xl">
        <div class="mb-5 flex items-start justify-between">
            <div>
                <h1 class="font-mono text-xl font-semibold">{{ transfer.reference_no }}</h1>
                <p class="text-sm text-ink-soft">{{ transfer.from_store?.name }} → {{ transfer.to_store?.name }}</p>
            </div>
            <StatusTag :label="TRANSFER_STATUSES[transfer.status]?.label ?? transfer.status"
                :tone="TRANSFER_STATUSES[transfer.status]?.tone" />
        </div>

        <div class="panel mb-4 grid grid-cols-3 gap-4 p-5 text-sm">
            <div>
                <p class="text-xs text-ink-faint">Requested by</p>
                <p class="text-ink">{{ transfer.requested_by_user?.name ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink-faint">Approved by</p>
                <p class="text-ink">{{ transfer.approved_by_user?.name ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-ink-faint">Received by</p>
                <p class="text-ink">{{ transfer.received_by_user?.name ?? '' }}</p>
            </div>
        </div>

        <p v-if="transfer.remarks" class="mb-4 text-sm text-ink-soft">"{{ transfer.remarks }}"</p>

        <div class="panel p-5">
            <h2 class="mb-3 text-sm font-semibold text-ink">Items</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Requested</th>
                        <th v-if="mode !== 'view' || transfer.status !== 'PENDING'">Sent</th>
                        <th v-if="mode === 'receiving' || ['RECEIVED', 'PARTIALLY_RECEIVED'].includes(transfer.status)">
                            Received</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in transfer.items" :key="item.id">
                        <td>
                            <p class="font-medium text-ink">{{ item.product_variant?.product?.name }}</p>
                            <p class="text-xs text-ink-faint">{{ item.product_variant?.name }} · {{
                                item.product_variant?.sku }}</p>
                        </td>
                        <td class="figures">{{ formatNumber(item.quantity_requested) }}</td>
                        <td v-if="mode === 'sending'">
                            <input v-model="workingItems[index].quantity_sent" type="number" min="0"
                                class="field-input w-24" />
                        </td>
                        <td v-else-if="mode !== 'view' || transfer.status !== 'PENDING'" class="figures">
                            {{ item.quantity_sent != null ? formatNumber(item.quantity_sent) : '' }}
                        </td>
                        <td v-if="mode === 'receiving'">
                            <input v-model="workingItems[index].quantity_received" type="number" min="0"
                                class="field-input w-24" />
                        </td>
                        <td v-else-if="['RECEIVED', 'PARTIALLY_RECEIVED'].includes(transfer.status)" class="figures">
                            {{ item.quantity_received != null ? formatNumber(item.quantity_received) : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <template v-if="mode === 'view'">
                <button v-if="canCancel" type="button" class="btn-secondary text-brick-500" @click="handleCancel">Cancel
                    transfer</button>
                <button v-if="canApprove" type="button" class="btn-primary" @click="handleApprove">Approve</button>
                <button v-if="canSend" type="button" class="btn-primary" @click="startSending">Record dispatch</button>
                <button v-if="canReceive" type="button" class="btn-primary" @click="startReceiving">Record
                    receipt</button>
            </template>
            <template v-else>
                <button type="button" class="btn-secondary" @click="mode = 'view'">Cancel</button>
                <button v-if="mode === 'sending'" type="button" class="btn-primary" @click="confirmSend">Confirm
                    dispatch</button>
                <button v-if="mode === 'receiving'" type="button" class="btn-primary" @click="confirmReceive">Confirm
                    receipt</button>
            </template>
        </div>
    </div>
</template>
