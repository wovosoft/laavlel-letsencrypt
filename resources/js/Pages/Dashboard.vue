<template>
    <Head title="Dashboard"/>

    <Container class="py-2">
        <Row>
            <Col>
                <Button variant="dark" class="py-3" @click="isAccountShown=true">
                    <Cash size="2" class="text-primary"/>
                    <br/>
                    Check Balance
                </Button>
            </Col>
            <Col>
                <Button variant="dark" class="py-3" @click="isMiniStatementShown=true">
                    <Cash size="2" class="text-primary"/>
                    <br/>
                    Mini Statement
                </Button>
            </Col>
            <Col>
                <Button variant="dark" class="py-3" @click="isAccountDetailsShown=true">
                    <Cash size="2" class="text-primary"/>
                    <br/>
                    Account Details
                </Button>
            </Col>
        </Row>
    </Container>
    <CheckBalance v-model="isAccountShown"/>
    <MiniStatement v-model="isMiniStatementShown"/>
    <AccountDetails v-model="isAccountDetailsShown"/>
</template>
<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import {Button, Col, Container, Row} from "@wovosoft/wovoui";
import {Cash} from "@wovosoft/wovoui-icons";
import {ref} from "vue";
import CheckBalance from "@/Components/Cbs/CheckBalance.vue";
import MiniStatement from "@/Components/Cbs/MiniStatement.vue";
import AccountDetails from "@/Components/Cbs/AccountDetails.vue";

const isAccountShown = ref<boolean>(false);
const isMiniStatementShown = ref<boolean>(false);
const isAccountDetailsShown = ref<boolean>(false);
</script>

