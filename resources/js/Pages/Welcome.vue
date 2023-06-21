<script setup lang="ts">
import {Head, useForm} from '@inertiajs/vue3';
import {Button, Card, Container, FormGroup, Input, RadioGroup, Tags} from "@wovosoft/wovoui";
import {ref} from "vue";


defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const validationMethods = [
    {text: 'HTTP', value: 'http-01'},
    {text: 'DNS', value: 'dns-01'},
];

const model = useForm({
    domains: [],
    email: null,
    method: 'http-01'
});

const theForm = ref<HTMLFormElement>();

function handleSubmission() {
    if (theForm.value?.reportValidity()) {
        model.post(route('certificates.create-order'), {
            onSuccess: (page) => {
                console.log(page)
            },
            onError: (errors) => {
                console.log(errors)
            }
        })
    }
}
</script>

<template>
    <Head title="Welcome"/>

    <Container>
        <form @submit.prevent="handleSubmission" ref="theForm">
            <Card class="mt-3" footer-class="text-end">
                <FormGroup label="Domain Names *">
                    <Tags
                        required
                        placeholder="Add Domain"
                        v-model="model.domains"
                    />
                </FormGroup>
                <FormGroup label="Email Address *">
                    <Input
                        required
                        size="sm"
                        type="email"
                        placeholder="Email Address"
                        v-model="model.email"
                    />
                </FormGroup>
                <FormGroup label="Verification Method">
                    <RadioGroup
                        :options="validationMethods"
                        text-field="text"
                        value-field="value"
                        v-model="model.method"
                    />
                </FormGroup>
                <template #footer>
                    <Button variant="danger" type="submit" size="sm" style="min-width: 300px;">
                        Submit
                    </Button>
                </template>
            </Card>
        </form>
    </Container>
</template>
