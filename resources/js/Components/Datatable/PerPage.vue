<script setup lang="ts">

import {InputGroup, Select} from "@wovosoft/wovoui";
import {router} from "@inertiajs/vue3";
import {PropType} from "vue";
import {DatatableType} from "@/types";

function pageChanged(e) {
    let url = new URL(window.location.href);
    url.searchParams.set("per_page", e.target.value);
    url.searchParams.delete("page");
    router.visit(url.href);
}

const props = defineProps({
    pages: {
        type: Object as PropType<DatatableType<any>>
    },
    options: {
        type: Array,
        default: () => [10, 15, 20, 50, 100, 500, 1000]
    }
});
</script>

<template>
    <InputGroup prepend="Per Page" size="sm">
        <Select
            @input="pageChanged"
            :options="options"
            :model-value="props.pages?.per_page"
        />
    </InputGroup>
</template>
