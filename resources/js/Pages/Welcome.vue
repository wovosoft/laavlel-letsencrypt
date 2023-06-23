<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';

import {Card, Container} from "@wovosoft/wovoui";
</script>

<template>
    <Head title="Welcome"/>

    <Container>
        <Card class="mt-3 text-center">
            <Link class="btn btn-primary" :href="route('guest-certificates.create-order')">
                Generate Free SSL
            </Link>
        </Card>
    </Container>
</template>
