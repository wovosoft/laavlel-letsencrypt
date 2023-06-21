<script setup lang="ts">
import {useForm} from '@inertiajs/vue3';
import {nextTick, ref} from 'vue';
import {Button, Modal, Card, Input, FormGroup, Feedback} from "@wovosoft/wovoui";
import route from "ziggy-js";

const confirmingUserDeletion = ref<boolean>(false);
const passwordInput = ref<InstanceType<typeof Input>>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.$el.focus());
};

const deletingForm = ref<HTMLFormElement>(null);
const deleteUser = () => {
    if (deletingForm.value?.reportValidity()) {
        form.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.value?.$el.focus(),
            onFinish: () => form.reset(),
        });
    }
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <Card>
        <template #header>
            <h2>Delete Account</h2>
            <p>
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting
                your account, please download any data or information that you wish to retain.
            </p>
        </template>

        <Button size="sm" variant="danger" @click="confirmUserDeletion">Delete Account</Button>

        <Modal v-model="confirmingUserDeletion"
               lazy
               shrink
               ok-title="Delete"
               @ok.prevent="deleteUser"
               :ok-button-options="{variant:'danger'}"
               :loading="form.processing"
               header-variant="danger"
               close-btn-white
               header="Are you sure you want to delete your account?"
               @hidden="closeModal">
            <form ref="deletingForm" class="p-6">
                <p class="mt-1 text-sm text-gray-600">
                    Once your account is deleted, all of its resources and data will be permanently deleted. Please
                    enter your password to confirm you would like to permanently delete your account.
                </p>

                <FormGroup lang="Password">
                    <Input
                        size="sm"
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                        required
                        :state="!!form.errors.password?false:null"
                    />

                    <Feedback type="invalid" :message="form.errors.password"/>
                </FormGroup>
            </form>
        </Modal>
    </Card>
</template>
