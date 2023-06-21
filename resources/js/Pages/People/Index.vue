<script setup lang="ts">
import {PropType, ref} from "vue";
import {Container, DataTable, FormGroup, Input, Modal, Textarea,} from "@wovosoft/wovoui";
import {DatatableType} from "@/types";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import {router, useForm} from "@inertiajs/vue3";
import route from "ziggy-js";
import {addToast} from "@/Composables/useToasts";

const props = defineProps({
    items: Object as PropType<DatatableType<Person>>,
    queries: Object as PropType<{ [key: string]: any }>
});

const fields = [
    {key: 'id'},
    {key: 'full_name', label: 'Name'},
    {key: 'phone'},
    {key: 'email'},
    {key: 'address'},
    {key: 'action', tdClass: 'text-end', thClass: 'text-end'},
];

const isView = ref<boolean>(false);
const isEdit = ref<boolean>(false);
const currentItem = ref<{ [key: string]: any } | null>(null);

const showItem = (item) => {
    currentItem.value = item;
    isView.value = true;
}

const formKeys = ['id', 'first_name', 'last_name', 'phone', 'email', 'address', 'dob'];
const formItem = useForm({
    id: null,
    first_name: null,
    last_name: null,
    phone: null,
    email: null,
    address: null,
    dob: null,
});

const editItem = (item) => {
    if (item) {
        formKeys.forEach(key => {
            formItem[key] = item[key];
        });
    }
    isEdit.value = true;
};

const deleteItem = (item) => {
    if (confirm("Are You Sure?")) {
        const options = {
            onSuccess: page => {
                addToast(page.props.notification);
            },
            onError: error => {
                console.log(error)
            }
        };

        router.delete(route('people.delete', {person: item.id}), options);
    }
}

const theForm = ref<HTMLFormElement>()
const handleSubmission = () => {
    if (theForm.value?.reportValidity()) {
        const options = {
            onSuccess: page => {
                console.log(page.props)
                addToast(page.props.notification);
                formItem.reset();
                isEdit.value = false;
            },
            onError: error => {
                console.log(error)
            }
        };

        if (formItem.id) {
            formItem.patch(route('people.update', {person: formItem.id}), options);
        } else {
            formItem.put(route('people.store'), options);
        }
    }
}

function onHiddenForm() {
    formItem.reset();
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
                        @click:edit="editItem(row.item)"
                        @click:delete="deleteItem(row.item)"
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
               @hidden="onHiddenForm"
               header-variant="dark"
               close-btn-white
               size="lg"
               ok-title="Submit"
               no-close-on-esc
               no-close-on-backdrop
               :loading="formItem.processing"
               @ok.prevent="handleSubmission"
               title="Person Details">
            <form class="row" ref="theForm">
                <FormGroup label="First Name *" class="col-md-6">
                    <Input size="sm" v-model="formItem.first_name" required placeholder="First Name"/>
                </FormGroup>
                <FormGroup label="Last Name" class="col-md-6">
                    <Input size="sm" v-model="formItem.last_name" placeholder="Last Name"/>
                </FormGroup>
                <FormGroup label="Phone" class="col-md-6">
                    <Input size="sm" v-model="formItem.phone" type="tel" placeholder="Phone"/>
                </FormGroup>
                <FormGroup label="Email Address" class="col-md-6">
                    <Input size="sm" v-model="formItem.email" type="email" placeholder="Email Address"/>
                </FormGroup>
                <FormGroup label="Date of Birth" class="col-md-6">
                    <Input size="sm" v-model="formItem.dob" type="date" placeholder="Date of Birth"/>
                </FormGroup>
                <FormGroup label="Address" class="col-md-6">
                    <Textarea size="sm" v-model="formItem.address" placeholder="Address"/>
                </FormGroup>
            </form>
        </Modal>
    </Container>
</template>
