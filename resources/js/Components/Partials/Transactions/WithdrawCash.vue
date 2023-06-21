<script setup lang="ts">
import {useForm} from "@inertiajs/vue3";
import {Col, FormGroup, Modal, Row} from "@wovosoft/wovoui";
import {useModel, PropType} from "vue";
import SelectPerson from "@/Components/Selectors/SelectPerson.vue";

const props = defineProps({
    show: {
        type: Boolean as PropType<boolean>,
        default: false
    }
});

const isShown = useModel(props, 'show');

const formData = useForm({
    account_id: null,
    person_id: null,
    amount: 0,
    message: null
});


</script>

<template>
    <Modal
        v-model="isShown"
        header-variant="dark"
        close-btn-white
        no-close-on-backdrop
        no-close-on-esc
        size="lg"
        title="Withdraw Cash"
        shrink>
        <form>
            <Row>
                <Col :md="6">
                    <FormGroup label="Person">
                        <SelectPerson v-model="formData.person_id"/>
                    </FormGroup>
                </Col>
            </Row>
        </form>
        <pre>{{ formData }}</pre>
    </Modal>
</template>

