<script setup lang="ts">
import {DataTable, FormGroup, Modal} from "@wovosoft/wovoui";
import SelectAccount from "@/Components/Selectors/SelectAccount.vue";
import {PropType, reactive, useModel} from "vue";
import axios from "axios";

const props = defineProps({
    modelValue: {
        type: Boolean as PropType<boolean>,
        default: false
    }
});

const isAccountShown = useModel(props, 'modelValue');

const model = reactive({
    account_id: null,
    loading: false,
    output: []
});
const handleSubmission = () => {
    if (!model.account_id) {
        return;
    }

    model.loading = true;

    axios.post(route('accounts.mini-statement', {account: Number(model.account_id)}))
        .then(res => {
            model.output = res.data;
        })
        .catch(err => {
            console.log(err.response)
            model.output = [];
        })
        .finally(() => {
            model.loading = false;
        });
};

function onHidden() {
    model.output = [];
    model.loading = false;
    model.account_id = null;
}

const fields = [
    {key: "TraceNo"},
    {key: "TrnDate"},
    {
        key: "Withdrawal",
        tdClass: 'text-end',
        thClass: 'text-end',
        formatter: (v: { [x: string]: any; }, k: string | number) => `${v[k]} ${v['CurrCode']}`
    },
    {
        key: "Deposit",
        tdClass: 'text-end',
        thClass: 'text-end',
        formatter: (v: { [x: string]: any; }, k: string | number) => `${v[k]} ${v['CurrCode']}`
    },
    {
        key: "Balance",
        tdClass: 'text-end',
        thClass: 'text-end',
        formatter: (v: { [x: string]: any; }, k: string | number) => `${v[k]} ${v['CurrCode']}`
    },
];
</script>

<template>
    <Modal v-model="isAccountShown"
           shrink
           header-variant="dark"
           close-btn-white
           ok-title="Mini Statement"
           @ok.prevent="handleSubmission"
           @hidden="onHidden"
           :loading="model.loading"
           no-close-button
           title="Get Mini Statement">
        <FormGroup label="Select Account">
            <SelectAccount v-model="model.account_id"></SelectAccount>
        </FormGroup>

        <DataTable
            small
            bordered
            head-class="table-dark"
            striped
            :items="model.output"
            :fields="fields"
        />
    </Modal>
</template>
