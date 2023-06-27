<script setup lang="ts">
import {computed, PropType, ref} from "vue";
import {Container, DataTable, FormGroup, Input, Modal} from "@wovosoft/wovoui";
import {DatatableType} from "@/types";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import {useForm} from "@inertiajs/vue3";
import route from "ziggy-js";
import {toDateTime} from "@/Composables/useHelpers";

const props = defineProps({
    items: Object as PropType<DatatableType<Account>>,
    queries: Object as PropType<{ [key: string]: any }>
});


const fields = computed(() => [
    {key: 'id'},
    {key: 'user', formatter: (v, k) => v[k]?.name},
    {key: 'account_no'},
    {key: 'account_name'},
    {key: 'created_at', formatter: (v, k) => toDateTime(v[k])},
    {key: 'status'},
    {key: 'action', tdClass: 'text-end', thClass: 'text-end'},
]);

const isView = ref<boolean>(false);
const isEdit = ref<boolean>(false);
const currentItem = ref<{ [key: string]: any } | null>(null);

const showItem = (item) => {
    currentItem.value = item;
    isView.value = true;
};

const formItem = useForm({
    id: null,
    account_no: null,
    account_name: null,
});

const formKeys = ['id', 'account_no', 'account_name'];
const editItem = (item) => {
    if (item) {
        formKeys.forEach(key => {
            formItem[key] = item[key];
        });
    }
    isEdit.value = true;
};

const theForm = ref<HTMLFormElement>();
const handleSubmission = () => {
    if (theForm.value?.reportValidity()) {
        const options = {
            onSuccess: page => {
                formItem.reset();
                isEdit.value = false;
            },
            onError: error => {
                console.log(error)
            }
        };

        if (formItem.id) {
            formItem.patch(route('accounts.update', {account: formItem.id}), options);
        } else {
            formItem.put(route('accounts.store'), options);
        }
    }
};
</script>

<template>
    <Container fluid class="pt-3">
        <BasicDatatable :items="items" :queries="queries" :fields="fields" @click:new="editItem(null)">
            <DataTable
                class="mb-0"
                head-class="table-dark"
                small
                bordered
                hover
                striped
                :items="items?.data"
                :fields="fields">
                <template #cell(action)="row">
                    <ActionButtons
                        @click:view="showItem(row.item)"
                        @click:edit="editItem(row.item)"
                        no-delete
                    />
                </template>
            </DataTable>
        </BasicDatatable>
        <Modal v-model="isView"
               shrink
               lazy
               @hidden="currentItem=null"
               header-variant="dark"
               close-btn-white
               size="lg"
               title="Person Details">
            <pre>{{ currentItem }}</pre>
        </Modal>
        <Modal v-model="isEdit"
               shrink
               lazy
               @hidden="formItem.reset()"
               header-variant="dark"
               close-btn-white
               size="lg"
               ok-title="Submit"
               no-close-on-esc
               no-close-on-backdrop
               :loading="formItem.processing"
               @ok.prevent="handleSubmission"
               title="Person Details">
            <form ref="theForm" @submit.prevent="handleSubmission">
                <FormGroup label="Account No. *">
                    <Input size="sm" required v-model="formItem.account_no" placeholder="Account No."/>
                </FormGroup>
                <FormGroup label="Account Name *">
                    <Input size="sm" required v-model="formItem.account_name" placeholder="Account Name"/>
                </FormGroup>
                <!--                <pre>{{ formItem }}</pre>-->
            </form>
        </Modal>
    </Container>
</template>
