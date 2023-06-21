<script setup lang="ts">
import {FormGroup, Modal} from "@wovosoft/wovoui";
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
    output: null
});
const handleSubmission = () => {
    if (!model.account_id) {
        return;
    }
    model.loading = true;
    axios.post(route('accounts.details', {account: Number(model.account_id)}))
        .then(res => {
            console.log(res.data)
            model.output = res.data;
        })
        .catch(err => {
            console.log(err.response)
            model.output = null;
        })
        .finally(() => {
            model.loading = false;
        });
};

function onHidden() {
    model.output = null;
    model.loading = false;
    model.account_id = null;
}
</script>

<template>
    <Modal v-model="isAccountShown"
           shrink
           header-variant="dark"
           close-btn-white
           ok-title="Get Details"
           @ok.prevent="handleSubmission"
           @hidden="onHidden"
           :loading="model.loading"
           no-close-button
           title="Account Details">
        <FormGroup label="Select Account">
            <SelectAccount v-model="model.account_id"></SelectAccount>
        </FormGroup>

        <div>{{ model.output }}</div>
    </Modal>
</template>
