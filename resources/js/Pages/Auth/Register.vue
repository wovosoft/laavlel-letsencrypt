<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import {Button, Card, Feedback, FormGroup, Input} from "@wovosoft/wovoui";

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register"/>

        <form @submit.prevent="submit" style="max-width: 400px;margin: auto;">
            <Card class="mt-5">
                <template #header>
                    Register
                </template>
                <FormGroup label="Name">
                    <Input
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        placeholder="Your Name"
                        autocomplete="name"
                        :state="!!form.errors.name?false:null"
                    />

                    <Feedback type="invalid" :message="form.errors.name"/>
                </FormGroup>

                <FormGroup label="Email">
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        placeholder="Your Email Address"
                        autocomplete="username"
                        :state="!!form.errors.email?false:null"
                    />

                    <Feedback type="invalid" :message="form.errors.email"/>
                </FormGroup>

                <FormGroup label="Password">
                    <Input
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        placeholder="Write down a strong password"
                        autocomplete="new-password"
                        :state="!!form.errors.password?false:null"
                    />

                    <Feedback :message="form.errors.password"/>
                </FormGroup>

                <FormGroup label="Confirm Password">
                    <Input
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        required
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                        :state="!!form.errors.password_confirmation?false:null"
                    />

                    <Feedback type="invalid" :message="form.errors.password_confirmation"/>
                </FormGroup>

                <div class="flex items-center justify-end mt-4">
                    <Link :href="route('login')" class="text-link">
                        Already registered?
                    </Link>

                    <Button variant="primary" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                        Register
                    </Button>
                </div>
            </Card>
        </form>
    </GuestLayout>
</template>
