<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import {PropType, useAttrs} from "vue";

const props = defineProps({
    href: String as PropType<string>
});

const attrs = useAttrs();
</script>

<template>
    <li class="nav-item">
        <Link class="nav-link" :href="href" v-bind="attrs">
            <slot></slot>
        </Link>
    </li>
</template>
