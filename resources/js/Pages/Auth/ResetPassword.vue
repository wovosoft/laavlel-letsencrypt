<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import {Button, Feedback, FormGroup, Input} from "@wovosoft/wovoui";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password"/>

        <form @submit.prevent="submit">
            <FormGroup label="Email">
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    :state="!!form.errors.email?false:null"
                />

                <Feedback type="invalid" class="mt-2" :message="form.errors.email"/>
            </FormGroup>

            <FormGroup label="Password">
                <Input
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    :state="!!form.errors.password?false:null"
                />

                <Feedback class="mt-2" :message="form.errors.password"/>
            </FormGroup>

            <FormGroup label="Confirm Password">
                <Input
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    :state="!!form.errors.password_confirmation?false:null"
                />

                <Feedback class="mt-2" :message="form.errors.password_confirmation"/>
            </FormGroup>

            <div class="flex items-center justify-end mt-4">
                <Button variant="primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reset Password
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
