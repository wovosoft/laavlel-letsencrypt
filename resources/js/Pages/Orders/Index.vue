<script setup lang="ts">
import {computed, PropType, ref} from "vue";
import {Button, Container, DataTable, FormGroup, Input, Modal} from "@wovosoft/wovoui";
import {DatatableType} from "@/types";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import {useForm} from "@inertiajs/vue3";
import route from "ziggy-js";
import {toDateTime} from "@/Composables/useHelpers";
import SelectDomain from "@/Components/Selectors/SelectDomain.vue";

const props = defineProps({
    items: Object as PropType<DatatableType<Account>>,
    queries: Object as PropType<{ [key: string]: any }>
});

const fields = computed(() => [
    {key: 'id'},
    {key: 'domain', formatter: (v, k) => v[k]?.domain},
    {key: 'created_at', formatter: (v, k) => toDateTime(v[k])},
    {key: 'action', tdClass: 'text-end', thClass: 'text-end'},
]);

const isView = ref<boolean>(false);
const isEdit = ref<boolean>(false);
const currentItem = ref<any>(null);

const showItem = (item) => {
    currentItem.value = item;
    isView.value = true;
};

const formItem = useForm({
    domain: null,
});

const editItem = (item) => {
    formItem.domain = null;
    isEdit.value = true;
};

const domain_id = ref<number>();
const theForm = ref<HTMLFormElement>();
const handleSubmission = () => {
    if (theForm.value?.reportValidity() && domain_id.value) {
        const options = {
            onSuccess: page => {
                formItem.reset();
                isEdit.value = false;
                console.log(page.props)
            },
            onError: error => {
                console.log(error)
            }
        };

        formItem.put(route('orders.store', {domain: domain_id.value}), options);
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
                        no-edit>
                        <template #prepend>
                            <Button>
                                Verify
                            </Button>
                        </template>
                    </ActionButtons>
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
               title="Domain Details">
            <h2>
                {{ currentItem.domain?.domain }}
            </h2>
            <div>
                Created At : {{ currentItem?.created_at }}
            </div>
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
               title="Domain Details">
            <form ref="theForm" @submit.prevent="handleSubmission">
                <FormGroup label="Account No. *">
                    <SelectDomain preload v-model="domain_id"/>
                </FormGroup>
                <!--                <pre>{{ formItem }}</pre>-->
            </form>
        </Modal>
    </Container>
</template>
