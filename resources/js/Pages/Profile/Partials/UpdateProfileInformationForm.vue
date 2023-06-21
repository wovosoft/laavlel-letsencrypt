<script setup>
import {Link, useForm, usePage} from '@inertiajs/vue3';
import {Button, Card, Feedback, FormGroup, Input} from "@wovosoft/wovoui";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <form @submit.prevent="form.patch(route('profile.update'))">
        <Card footer-class="d-flex justify-content-between">
            <template #header>
                <h2>Profile Information</h2>
                <p>
                    Update your account's profile information and email address.
                </p>
            </template>


            <FormGroup label="Name">
                <Input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    size="sm"
                    autocomplete="name"
                    :state="!!form.errors.name?false:null"
                />
                <Feedback type="invalid" :message="form.errors.name"/>
            </FormGroup>

            <FormGroup label="Email">
                <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    size="sm"
                    autocomplete="username"
                    :state="!!form.errors.email?false:null"
                />
                <Feedback type="invalid" :message="form.errors.email"/>
            </FormGroup>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        class="btn btn-primary"
                        :href="route('verification.send')"
                        method="post"
                        as="button">
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <template #footer>
                <Button size="sm" type="submit" variant="primary" :disabled="form.processing">Save</Button>

                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                    <div v-if="form.recentlySuccessful" class="text-sm text-primary">Saved.</div>
                </Transition>
            </template>
        </Card>
    </form>
</template>
