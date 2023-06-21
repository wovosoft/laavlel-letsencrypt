<template>
    <ButtonGroup :size="size">
        <slot name="prepend"/>
        <slot>
            <Link class="btn btn-dark" title="View" v-if="viewHref" :href="viewHref">
                <Spinner size="sm" v-if="viewLoading"/>
                <Eye v-else/>
            </Link>
            <Button v-else variant="dark" title="View" @click="e=>$emit('click:view',e)" v-if="!noView">
                <Spinner size="sm" v-if="viewLoading"/>
                <Eye v-else/>
            </Button>
            <Button variant="primary" title="Edit" @click="e=>$emit('click:edit',e)" v-if="!noEdit">
                <Pencil/>
            </Button>
            <Button variant="danger" title="Delete" @click="e=>$emit('click:delete',e)" v-if="!noDelete">
                <Trash/>
            </Button>
        </slot>
        <slot name="append"/>
    </ButtonGroup>
</template>

<script lang="ts" setup>
import {PropType} from "vue";
import {ButtonGroup, Button, Spinner} from "@wovosoft/wovoui";
import {Eye, Pencil, Trash} from "@wovosoft/wovoui-icons";
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    size: {
        type: String as PropType<'sm' | 'lg'>,
        default: 'sm'
    },
    noView: {
        type: Boolean as PropType<boolean>,
        default: false
    },
    noEdit: {
        type: Boolean as PropType<boolean>,
        default: false
    },
    noDelete: {
        type: Boolean as PropType<boolean>,
        default: false
    },
    viewHref: {
        type: String as PropType<string>,
        default: null
    },
    viewLoading: {
        type: Boolean as PropType<boolean>,
        default: false
    },
});

const emit = defineEmits<{
    (e: 'click:view', value: unknown): void;
    (e: 'click:edit', value: unknown): void;
    (e: 'click:delete', value: unknown): void;
}>();
</script>
