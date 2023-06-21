<script setup lang="ts">
import {PropType} from "vue";
import {
    Button, ButtonGroup, Card, Col, Input, Row
} from "@wovosoft/wovoui";
import {DatatableType} from "@/types";
import PerPage from "@/Components/Datatable/PerPage.vue";
import PageLinks from "@/Components/Datatable/PageLinks.vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    title: String as PropType<string>,
    items: Object as PropType<DatatableType<any>>,
    fields: Array,
    queries: Object as PropType<{ [key: string]: any }>,
    actionCols: {type: Number as PropType<number>, default: () => 2}
});

function pageChanged(e) {
    let url = new URL(window.location.href);
    url.searchParams.set("query", e.target.value);
    url.searchParams.delete('page');
    router.visit(url.href);
}
</script>

<template>
    <h4 class="mt-2">{{ $page.props.title }}</h4>
    <Card body-class="p-2" class="mb-3" v-bind="$attrs">
        <template #header>
            <Row>
                <Col :md="2">
                    <PerPage :pages="items"/>
                </Col>
                <Col>
                    <Input
                        @change="pageChanged"
                        type="search"
                        placeholder="Search..."
                        size="sm"
                        :value="$page.props?.queries?.query"
                    />
                </Col>
                <Col :md="actionCols" class="text-end">
                    <ButtonGroup size="sm">
                        <slot name="actions">
                            <Button variant="dark" @click="$emit('click:new')">New</Button>
                        </slot>
                    </ButtonGroup>
                </Col>
            </Row>
        </template>
        <slot/>
        <template #footer>
            <PageLinks :pages="items"/>
        </template>
    </Card>
</template>
