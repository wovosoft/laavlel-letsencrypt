<template>
    <Row jc="between" v-if="pages">
        <Col :md="6" :sm="12" class="text-muted small">
            Showing {{ pages.data.length }}
            items from {{ pages.from }}
            to {{ pages.to }} of {{ pages.total }} items
        </Col>
        <Col :md="6" :sm="12">
            <Pagination size="sm" :class="paginationClass" class="justify-content-md-end mb-0">
                <InertiaPageItem v-if="pages.prev_page_url" :href="pages.prev_page_url">
                    &laquo;
                </InertiaPageItem>
                <template v-for="page_link in pages.links.slice(1,pages.links.length-1)">
                    <InertiaPageItem
                        v-if="page_link.url"
                        :active="page_link.active"
                        :href="page_link.url">
                        {{ page_link.label }}
                    </InertiaPageItem>
                </template>

                <InertiaPageItem v-if="pages.next_page_url" :href="pages.next_page_url">
                    &raquo;
                </InertiaPageItem>
            </Pagination>
        </Col>
    </Row>
</template>

<script lang="ts" setup>
import InertiaPageItem from "./InertiaPageItem.vue";
import {Col, Pagination, Row} from "@wovosoft/wovoui";
import {PropType} from "vue";
import {DatatableType} from "@/types";

defineProps({
    pages: {type: Object as PropType<DatatableType<any>>},
    paginationClass: {}
});
</script>

