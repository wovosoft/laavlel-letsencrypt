<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import {PropType, useAttrs} from "vue";

const attrs = useAttrs();

defineProps({
    href: {
        type: String as PropType<string>,
        required: true,
    },
});
</script>

<template>
    <li role="presentation">
        <Link :href="href" class="dropdown-item" v-bind="attrs">
            <slot/>
        </Link>
    </li>
</template>
