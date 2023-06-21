<template>
    <InputGroup size="sm" :class="{'is-invalid':state?null:false}">
        <TypeHead
            preload
            v-bind="$attrs"
            class="form-control p-0"
            toggle-class="border-0"
            v-model="model"
            toggle-size="sm"
            :get-option="(op:Person) => op?.full_name"
            :api-url="route('people.options')">
            <template #label>
                {{ model?.first_name || 'Select Person' }}
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
import {PropType, WritableComputedRef} from "vue";
import route from "ziggy-js";
import {useSelectorModel} from "@/Composables/useHelpers";

const props = defineProps({
    state: {
        type: Boolean as PropType<boolean>,
        default: false
    },
    person: {
        type: Object as PropType<Person>,
        default: null
    },
    modelValue: {
        type: Number as PropType<number | null>,
        default: null
    },
});

const model: WritableComputedRef<Person> = useSelectorModel(
    props,
    'modelValue',
    props.person,
    'id'
);

</script>
