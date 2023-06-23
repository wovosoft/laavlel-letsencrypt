<script setup lang="ts">
import {Head, useForm} from '@inertiajs/vue3';
import {Button, Card, Container, FormGroup, Input, RadioGroup, Spinner, Tags} from "@wovosoft/wovoui";
import {ref} from "vue";

const validationMethods = [
    {text: 'HTTP', value: 'http-01'},
    {text: 'DNS', value: 'dns-01'},
];

const model = useForm({
    domains: [
        "wovo.xyz"
    ],
    email: "narayanadhikary24@gmail.com",
    method: 'http-01'
});

const theForm = ref<HTMLFormElement>();

function handleSubmission() {
    if (theForm.value?.reportValidity()) {
        model.post(route('guest-certificates.authorize-order'), {
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
    <Head title="Guest Order"/>

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
                        name="email"
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
                        <Spinner v-if="model.processing" size="sm"/>
                        Submit
                    </Button>
                </template>
            </Card>
        </form>
    </Container>
</template>
