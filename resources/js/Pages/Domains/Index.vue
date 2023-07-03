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

                    </ActionButtons>
                </template>
            </DataTable>
        </BasicDatatable>
        <Modal v-model="isView"
               shrink
               lazy
               @hidden="onHiddenOrderAuthorization"
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
                    <Th>Order ID</Th>
                    <Th>Created At</Th>
                    <Th>Expires At</Th>
                    <Th class="text-end">Action</Th>
                </Tr>
                </THead>
                <TBody>
                <Tr v-for="order in currentItem?.orders">
                    <Td>{{ order?.order_id }}</Td>
                    <Td>{{ toDateTime(order?.created_at) }}</Td>
                    <Td>{{ toDateTime(order?.expires) }}</Td>
                    <Td class="text-end">
                        <ButtonGroup size="sm">
                            <Button variant="primary" @click="getAuthorizationMethods(order.id)">
                                <Spinner v-if="verificationMethods.processing" size="sm"/>
                                Challenges
                            </Button>
                        </ButtonGroup>
                    </Td>
                </Tr>
                </TBody>
            </Table>
            <!--            <pre>{{ currentItem }}</pre>-->

            <template v-if="orderAuthorizations?.length">
                <template v-for="oa in orderAuthorizations">
                    <Card v-for="challenge in oa.challenges" class="mb-3">
                        <template #header>
                            <Flex jc="between">
                                <FlexItem>{{ challenge.type }}</FlexItem>
                                <FlexItem>{{ challenge.status }}</FlexItem>
                            </Flex>
                        </template>
                        <template v-if="challenge.type==='http-01'">
                            <h4>Step-1: Download the file</h4>
                            <Button class="mt-3" variant="primary" size="sm" @click="saveFile(oa.file)">
                                Download File
                            </Button>

                            <p class="text-muted small mt-3">
                                While downloading file, if <code>.txt</code> extension
                                appears automatically, please remove the extension then save the file.
                            </p>
                        </template>
                        <template v-else-if="challenge.type==='dns-01'">
                            {{ oa.txt_record }}
                        </template>
                        <template v-else>
                            tls-01 (Pending)
                        </template>
                    </Card>
                </template>
            </template>
            <pre>{{ orderAuthorizations }}</pre>
        </Modal>
        <Modal v-model="isEdit"
               shrink
               lazy
               @hidden="()=> formItem.reset()"
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
    </Container>
</template>

<script setup lang="ts">
import {PropType, ref} from "vue";
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
    THead, Th, ButtonGroup, Card, Flex, FlexItem
} from "@wovosoft/wovoui";
import {AuthorizationFile, DatatableType, OrderAuthorization} from "@/types";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import {useForm} from "@inertiajs/vue3";
import route from "ziggy-js";
import {toDateTime} from "@/Composables/useHelpers";
import SelectAccount from "@/Components/Selectors/SelectAccount.vue";
import {CheckCircle, XCircle} from "@wovosoft/wovoui-icons";
import useAxiosForm from "@/Composables/useAxiosForm";
import {saveAs} from "file-saver";

const props = defineProps({
    items: Object as PropType<DatatableType<App.Models.Domain>>,
    queries: Object as PropType<{ [key: string]: any }>
});


const fields = [
    {key: 'id'},
    {key: 'account', formatter: (v, k) => v[k]?.email},
    {key: 'domain'},
    {key: 'is_ownership_verified', formatter: (v, k) => v[k] ? 'Yes' : 'No'},
    {key: 'created_at', formatter: (v, k) => toDateTime(v[k])},
    {key: 'action', tdClass: 'text-end', thClass: 'text-end'},
];

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

const verificationMethods = useAxiosForm({});

const orderAuthorizations = ref<OrderAuthorization[] | null>(null);

function getAuthorizationMethods(id: number) {
    if (!verificationMethods.processing) {
        verificationMethods
            .post(route('orders.get-authorizations', {order: id}))
            .then(res => {
                orderAuthorizations.value = res.data;
            })
    }
}

function onHiddenOrderAuthorization() {
    orderAuthorizations.value = null;
    currentItem.value = null;
}

const saveFile = (file: AuthorizationFile) => {
    const blob = new Blob([file.contents], {type: "text/plain;charset=utf-8"});
    saveAs(blob, file.filename);
};
</script>
