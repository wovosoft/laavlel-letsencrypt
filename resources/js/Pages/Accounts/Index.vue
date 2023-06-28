<script setup lang="ts">
import {computed, PropType, ref} from "vue";
import {Button, Container, DataTable, Feedback, FormGroup, Input, Modal, Spinner} from "@wovosoft/wovoui";
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
    {key: 'account_id'},
    {key: 'email'},
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
    email: null,
});


const editItem = (item) => {
    formItem.email = null;
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

        formItem.put(route('accounts.store'), options);
    }
};

const veryForm = useForm({
    account_id: null
});

function verifyAccount(account_id) {
    veryForm.account_id = account_id;
    veryForm.post(route('accounts.verify', {account: Number(account_id)}), {
        onSuccess: (page) => {
            console.log(page)
        },
        onError: (errors) => {
            console.log(errors)
        },
        onFinish: () => {
            veryForm.account_id = null;
        }
    })
}
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
                            <Button variant="primary" @click="verifyAccount(row.item.id)" v-if="!row.item.is_valid">
                                <Spinner size="sm" v-if="veryForm.processing && row.item.id===veryForm.account_id"/>
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
               title="Account Details">
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
                <FormGroup label="Email *">
                    <Input size="sm"
                           required
                           v-model="formItem.email"
                           type="email"
                           name="email"
                           placeholder="Email Address"
                           :class="{'is-invalid':!!formItem.errors.email}"
                    />
                    <Feedback :message="formItem.errors.email" type="invalid"/>
                </FormGroup>
                <!--                <pre>{{ formItem }}</pre>-->
            </form>
        </Modal>
    </Container>
</template>
