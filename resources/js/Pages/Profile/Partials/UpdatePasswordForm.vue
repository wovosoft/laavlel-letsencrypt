<script setup lang="ts">
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import {Button, Card, Feedback, FormGroup, Input} from "@wovosoft/wovoui";
import route from "ziggy-js";

const passwordInput = ref<InstanceType<typeof Input>>(null);
const currentPasswordInput = ref<InstanceType<typeof Input>>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.$el.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.$el.focus();
            }
        },
    });
};
</script>

<template>
    <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
        <Card footer-class="d-flex">
            <template #header>
                <h2>Update Password</h2>
                <p>
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </template>

            <FormGroup label="Current Password">
                <Input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    size="sm"
                    autocomplete="current-password"
                    :state="!!form.errors.current_password?false:null"
                />
                <Feedback type="invalid" :message="form.errors.current_password"/>
            </FormGroup>

            <FormGroup label="New Password">
                <Input
                    size="sm"
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    :state="!!form.errors.password_confirmation?false:null"
                />
            </FormGroup>

            <FormGroup label="Confirm Password" :invalid-feedback="form.errors.password_confirmation">
                <Input
                    size="sm"
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                />
            </FormGroup>

            <template #footer>
                <Button size="sm" variant="primary" type="submit" :disabled="form.processing">Save</Button>

                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                    <div v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</div>
                </Transition>
            </template>
        </Card>
    </form>
</template>
