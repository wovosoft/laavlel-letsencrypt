<script setup lang="ts">
import DropdownLink from '@/Components/DropdownLink.vue';
import {
    ButtonClose, Collapse, Nav, Navbar, NavItemDropdown, Toast, ToastBody, ToastContainer
} from "@wovosoft/wovoui";
import {Link} from "@inertiajs/vue3";
import route from "ziggy-js";
import {removeToast, toasts} from "@/Composables/useToasts";
import NavItemLink from "@/Components/NavItemLink.vue";
</script>

<template>
    <Navbar variant="dark" bg-variant="dark" class="py-0">
        <template #brand>
            <Link :href="route('dashboard')" class="navbar-brand">
                Lets Encrypt SSL
            </Link>
        </template>
        <template #default="{collapsed}">
            <Collapse is-nav :visible="collapsed">
                <Nav class="me-auto" navs>
                    <NavItemLink :href="route('accounts.index')">
                        Accounts
                    </NavItemLink>
                    <NavItemLink :href="route('domains.index')">
                        Domains
                    </NavItemLink>
                    <NavItemLink :href="route('orders.index')">
                        Orders
                    </NavItemLink>
                </Nav>
                <Nav navs>
                    <NavItemDropdown :text="$page.props.auth?.user?.name">
                        <!--                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>-->
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </NavItemDropdown>
                </Nav>
            </Collapse>
        </template>
    </Navbar>
    <slot/>
    <ToastContainer
        placement="top-right"
        container="body"
        class="position-fixed pt-2 pe-2"
        style="z-index: 9999">
        <Toast v-for="toast in toasts" :key="toast.key" show :variant="toast?.variant||'primary'" no-body
               class="text-white" :timeout="3">
            <div class="d-flex">
                <ToastBody>
                    {{ toast?.message }}
                </ToastBody>
                <ButtonClose class="me-2 m-auto" white @click="removeToast(toast.key)"/>
            </div>
        </Toast>
    </ToastContainer>
</template>
