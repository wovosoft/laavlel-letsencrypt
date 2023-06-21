<script setup lang="ts">
import {PropType, ref} from "vue";
import {DatatableType} from "@/types";
import {Button, Container, DataTable} from "@wovosoft/wovoui";
import ActionButtons from "@/Components/ActionButtons.vue";
import BasicDatatable from "@/Components/Datatable/BasicDatatable.vue";
import WithdrawCash from "@/Components/Partials/Transactions/WithdrawCash.vue";

const props = defineProps({
    items: Object as PropType<DatatableType<Transaction>>,
    queries: Object as PropType<{
        [key: string]: any
    }>
});

const fields = [
    {key: 'id'},
    {key: 'account'},
    {key: 'amount'},
    {key: 'status'},
    {key: 'type'},
    {key: 'created_at'},
    {key: 'action'}
];

const isFormShown = ref<boolean>(false);
</script>

<template>
    <Container fluid>
        <BasicDatatable :action-cols="4" :items="items" :queries="queries" :fields="fields">
            <template #actions>
                <Button>Deposit</Button>
                <Button variant="primary" @click="isFormShown=true">
                    Withdraw
                </Button>
                <Button>Transfer</Button>
            </template>
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
                    <!--                    <ActionButtons-->
                    <!--                        @click:view="showItem(row.item)"-->
                    <!--                        @click:edit="editItem(row.item)"-->
                    <!--                        @click:delete="deleteItem(row.item)"-->
                    <!--                    />-->
                </template>
            </DataTable>
        </BasicDatatable>
    </Container>
    <WithdrawCash v-model="isFormShown">

    </WithdrawCash>
</template>
