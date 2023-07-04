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
               size="xl"
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

                            <h4>Step-2: Upload File</h4>
                            <p>
                                Create a folder <code>.well-known</code> in the root folder of your domain.
                                And inside the <code>.well-known</code>"create another folder
                                <code>acme-challenge</code>. Then upload the above file(s) inside the
                                <code>acme-challenge</code>
                                folder.
                            </p>

                            <h4>Step-3: Check File</h4>
                            <p>
                                <a :href="getAuthFileUrl(oa)">
                                    {{ getAuthFileUrl(oa) }}
                                </a>
                            </p>
                            <h4>Step-4: Verify Domain Ownership</h4>
                            <Button variant="primary" size="sm" class="mt-3"
                                    @click="validateHttpChallenge(oa)">
                                Verify Domain
                            </Button>
                        </template>
                        <template v-else-if="challenge.type==='dns-01'">
                            <ol>
                                <li>
                                    Login to your domain host (or wherever service that is "in control" of your domain).
                                </li>
                                <li>
                                    Go to the DNS record settings and create a new TXT record.
                                </li>
                                <li>
                                    In the Name/Host/Alias field, enter the domain TXT record from below table for
                                    example: "_acme-challenge".
                                </li>
                                <li>
                                    In the Value/Answer field enter the verfication code from below table.
                                </li>
                                <li>
                                    Wait for few minutes for the TXT record to propagate. You can check if it worked by
                                    clicking on the "Check DNS" button. If you have multiple entries, make sure all of
                                    them are ok.
                                </li>
                            </ol>
                            <Table small bordered>
                                <THead variant="dark">
                                <Tr>
                                    <Th>TXT Record</Th>
                                    <Th>Value</Th>
                                    <Th>Status</Th>
                                </Tr>
                                </THead>
                                <TBody>
                                <Tr>
                                    <Td>{{ oa.txt_record.name }}</Td>
                                    <Td>{{ oa.txt_record.value }}</Td>
                                    <Td>
                                        <Button size="sm" variant="primary">
                                            Check Status
                                        </Button>
                                    </Td>
                                </Tr>
                                </TBody>
                            </Table>
                        </template>
                        <template v-else-if="challenge.type==='tls-alpn-01'">
                            <span class="text-danger">Not suitable for everyone</span>
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
import {AuthorizationChallenge, AuthorizationFile, DatatableType, OrderAuthorization} from "@/types";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import {useForm} from "@inertiajs/vue3";
import route from "ziggy-js";
import {toDateTime} from "@/Composables/useHelpers";
import SelectAccount from "@/Components/Selectors/SelectAccount.vue";
import {CheckCircle, XCircle} from "@wovosoft/wovoui-icons";
import useAxiosForm from "@/Composables/useAxiosForm";
import {saveAs} from "file-saver";
import axios from "axios";

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

const currentOrderId = ref<number | null>();
const orderAuthorizations = ref<OrderAuthorization[] | null>();

function getAuthorizationMethods(id: number) {
    if (!verificationMethods.processing) {
        verificationMethods
            .post(route('orders.get-authorizations', {order: id}))
            .then(res => {
                currentOrderId.value = id;
                orderAuthorizations.value = res.data;
            })
    }
}

function onHiddenOrderAuthorization() {
    orderAuthorizations.value = null;
    currentItem.value = null;
    currentOrderId.value = null;
}

const saveFile = (file: AuthorizationFile) => {
    const blob = new Blob([file.contents], {type: "text/plain;charset=utf-8"});
    saveAs(blob, file.filename);
};

function getAuthFileUrl(oa: OrderAuthorization): string {
    return (new URL(oa.file.filename, 'http://' + oa.domain + '/.well-known/acme-challenge/')).href;
}

function validateHttpChallenge(oa: OrderAuthorization) {
    let auth_url = oa.challenges.find((challenge: AuthorizationChallenge) => challenge.type === 'http-01')?.authorizationURL;

    let url = route('orders.validate-challenge', {order: Number(currentOrderId.value)});
    axios.post(url, {auth_url}).then(res => {
        console.log(res.data)
    }).catch(error => {
        console.log(error.response.data)
    }).finally(() => {
        // currentOrderId.value = null;
    });
}
</script>
