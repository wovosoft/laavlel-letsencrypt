<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {Button, Card, Checkbox, Feedback, FormGroup, Input} from "@wovosoft/wovoui";
import route from "ziggy-js";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in"/>
        <form @submit.prevent="submit" style="max-width: 400px;margin: auto;">
            <Card class="mt-5" header="Login" footer-class="d-flex justify-content-between">
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
                        placeholder="Email Address"
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
                        placeholder="Password"
                        autocomplete="current-password"
                        :state="!!form.errors.password?false:null"
                    />

                    <Feedback type="invalid" class="mt-2" :message="form.errors.password"/>
                </FormGroup>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model="form.remember">
                            Remember me
                        </Checkbox>
                    </label>
                </div>

                <template #footer>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-link">
                        Forgot your password?
                    </Link>

                    <Button variant="primary"
                            type="submit"
                            size="sm"
                            :class="{'opacity-25': form.processing}"
                            :disabled="form.processing">
                        Log in
                    </Button>
                </template>
            </Card>
        </form>
    </GuestLayout>
</template>
