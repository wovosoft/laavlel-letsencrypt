<script setup lang="ts">
import {PropType} from "vue";
import {Card, Container, Textarea} from "@wovosoft/wovoui";

const props = defineProps({
    privateKey: String as PropType<string>,
    certificate: String as PropType<string>
});
</script>

<template>
    <Container class="pt-3">
        <Card header="Private Key">
            <pre>{{ privateKey }}</pre>
        </Card>

        <Card header="Private Key" class="mt-3">
            <pre>{{ certificate }}</pre>
        </Card>
    </Container>
</template>

