<script setup lang="ts">
import {computed, PropType, ref} from "vue";
import {
    Button,
    Container,
    DataTable,
    FormGroup,
    Input,
    Modal,
    Spinner,
    TBody,
    Td,
    Tr,
    Table,
    THead, Th, ButtonGroup
} from "@wovosoft/wovoui";
import {DatatableType} from "@/types";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import {useForm} from "@inertiajs/vue3";
import route from "ziggy-js";
import {toDateTime} from "@/Composables/useHelpers";
import SelectAccount from "@/Components/Selectors/SelectAccount.vue";
import {Check, CheckCircle, SendDash, XCircle} from "@wovosoft/wovoui-icons";

const props = defineProps({
    items: Object as PropType<DatatableType<Account>>,
    queries: Object as PropType<{ [key: string]: any }>
});


const fields = computed(() => [
    {key: 'id'},
    {key: 'account', formatter: (v, k) => v[k]?.email},
    {key: 'domain'},
    {key: 'is_ownership_verified', formatter: (v, k) => v[k] ? 'Yes' : 'No'},
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

const account_id = ref<number>();
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

        formItem.put(route('domains.store', {account: account_id.value}), options);
    }
};

const isShownVerificationModal = ref<boolean>(false);
const showVerificationModal = () => {
    isShownVerificationModal.value = true;
}

const verificationMethods = useForm({});

function getAuthorizationMethods(id: number) {
    if (!verificationMethods.processing) {
        verificationMethods.post(route('orders.get-authorizations', {order: id}), {
            onSuccess: (page) => {
                console.log(page.props)
            },
            onError: (errors) => {
                console.log(errors)
            }
        })
    }
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
                    <ActionButtons @click:view="showItem(row.item)" no-edit>
                        <template #prepend>
                            <Button @click="showVerificationModal()">
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
            <h4>
                {{ currentItem?.domain }}
                <CheckCircle class="ms-2 text-primary" v-if="currentItem?.is_ownership_verified"/>
                <XCircle v-else class="ms-2 text-danger"/>
            </h4>
            <small class="text-muted">{{ currentItem?.created_at }}</small>


            <Table bordered small hover striped class="mt-3">
                <THead variant="dark">
                <Tr>
                    <Th>Date</Th>
                    <Th>Expires At</Th>
                    <Th>Action</Th>
                </Tr>
                </THead>
                <TBody>
                <Tr v-for="order in currentItem?.orders">
                    <Td>{{ order?.created_at }}</Td>
                    <Td>{{ order?.expires }}</Td>
                    <Td>
                        <ButtonGroup size="sm">
                            <Button variant="primary" @click="getAuthorizationMethods(order.id)"
                                    :disabled="verificationMethods.processing">
                                <Spinner v-if="verificationMethods.processing" size="sm"/>
                                Challenges
                            </Button>
                        </ButtonGroup>
                    </Td>
                </Tr>
                </TBody>
            </Table>
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
               title="Domain Details">
            <form ref="theForm" @submit.prevent="handleSubmission">
                <FormGroup label="Account No. *">
                    <SelectAccount preload v-model="account_id"/>
                </FormGroup>
                <FormGroup label="Domain Name *">
                    <Input size="sm"
                           required
                           v-model="formItem.domain"
                           placeholder="Domain Name"
                           name="domain"
                    />
                </FormGroup>
                <!--                <pre>{{ formItem }}</pre>-->
            </form>
        </Modal>
        <Modal
            no-close-on-backdrop
            no-close-on-esc
            static
            ok-title="Submit"
            v-model="isShownVerificationModal"
            title="Verify Domain"
            size="xl"
            header-variant="dark"
            close-btn-white
            shrink>
            <h3>1. Get Verification Methods</h3>
            <Button variant="primary" @click="getAuthorizationMethods" :disabled="verificationMethods.processing">
                <Spinner size="sm" v-if="verificationMethods.processing"/>
                Request Let's Encrypt
            </Button>
        </Modal>
    </Container>
</template>
