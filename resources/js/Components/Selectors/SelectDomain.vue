<template>
    <InputGroup size="sm" :class="{'is-invalid':state?null:false}">
        <TypeHead
            :preload="preload"
            v-bind="$attrs"
            class="form-control p-0"
            toggle-class="border-0"
            v-model="model"
            toggle-size="sm"
            :get-option="(op:Account) => op?.domain"
            :api-url="route('domains.options')">
            <template #label>
                {{ model?.domain || 'Select Domain' }}
            </template>
        </TypeHead>
        <Button @click="model=null">
            <X/>
        </Button>
    </InputGroup>
</template>

<script lang="ts" setup>
import {Button, InputGroup, TypeHead} from "@wovosoft/wovoui";
import {X} from "@wovosoft/wovoui-icons";
import {PropType} from "vue";
import route from "ziggy-js";
import {useSelectorModel} from "@/Composables/useHelpers";

const props = defineProps({
    state: {
        type: Boolean as PropType<boolean>,
        default: false
    },
    initial: {
        type: Object as PropType<Account | null>,
        default: null
    },
    modelValue: {
        type: Number as PropType<number | null>,
        default: null
    },
    preload: {
        type: Boolean as PropType<boolean>,
        default: false
    }
});

const model = useSelectorModel(
    props,
    'modelValue',
    props.initial,
    'id'
);

</script>
