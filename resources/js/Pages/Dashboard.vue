<template>
    <Head title="Dashboard"/>

    <Container class="py-2">
        <Row>
            <Col>
                <Link href="#" class="py-3 btn btn-primary">
                    <Cash size="2" class="text-primary"/>
                    <br/>
                    Create SSL
                </Link>
            </Col>
        </Row>
    </Container>
</template>
<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import {Button, Col, Container, Row} from "@wovosoft/wovoui";
import {Cash} from "@wovosoft/wovoui-icons";
</script>

