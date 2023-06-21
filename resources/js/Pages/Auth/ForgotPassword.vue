<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, useForm} from '@inertiajs/vue3';
import {Button, Card, Feedback, FormGroup, Input} from "@wovosoft/wovoui";
import route from "ziggy-js";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password"/>

        <form @submit.prevent="submit" style="max-width: 400px;margin: auto;" class="mt-5">
            <Card>
                <div class="mb-4 text-sm text-gray-600">
                    Forgot your password? No problem. Just let us know your email address and we will email you a
                    password reset
                    link that will allow you to choose a new one.
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                    {{ status }}
                </div>
                <FormGroup label="Email">
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Email Address"
                        :state="!!form.errors.email?false:null"
                    />

                    <Feedback type="invalid" :message="form.errors.email"/>
                </FormGroup>

                <div class="flex items-center justify-end mt-4">
                    <Button variant="danger" type="submit"
                            :class="{ 'opacity-25': form.processing }"
                            size="sm"
                            :disabled="form.processing">
                        Email Password Reset Link
                    </Button>
                </div>
            </Card>
        </form>
    </GuestLayout>
</template>
