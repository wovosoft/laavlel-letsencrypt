<script setup lang="ts">
import {PropType, ref} from "vue";
import {Head, useForm} from "@inertiajs/vue3";
import {Button, Card, Container, Spinner} from "@wovosoft/wovoui";
import {saveAs} from "file-saver";

interface Challenge {
    url: string
    status: string
    type: "http-01" | "dns-01" | "tls-alpn-01",
    token: string
    authorizationURL: string
}

interface AuthorizationFile {
    name: string
    contents: string
}

const props = defineProps({
    title: String as PropType<string>,
    order: Object as PropType<{
        id: string
        email: string
        domains: string[],
        authorizations: {
            id: string
            file: AuthorizationFile,
            domain: string
            txt_record: {
                name: string
                value: string
            },
            expires_at: {
                date: string
                timezone_type: number
                timezone: string
            },
            authorization_url: string
            challenges: Challenge[]
        }[]
    }>
});

const saveFile = (file: AuthorizationFile) => {
    const blob = new Blob([file.contents], {type: "text/plain;charset=utf-8"});
    saveAs(blob, file.name);
};

const processing = ref<boolean>(false);
const validateDomain = (challenge: Challenge) => {
    processing.value = true;
    useForm({
        challenge,
        email: props.order.email,
        order_id: props.order.id
    }).post(route('guest-certificates.validate-domain'), {
        onSuccess: (page) => {
            console.log(page)
        },
        onError: (errors) => {
            console.log(errors)
        },
        onFinish: () => {
            processing.value = false
        }
    })
};
</script>

<template>
    <Head title="Challenges"/>
    <Container>
        <Card class="mt-3 m-auto" :header="order.domains.join(', ')" style="max-width: 800px;">
            <div v-for="authorization in order.authorizations">
                <h4><span class="text-muted">Let's verify that you own</span> {{ authorization.domain }}</h4>
                <div class="my-3">Expires At : {{ authorization.expires_at.date }}</div>

                <ol style="line-height: 2.5rem;">
                    <li>
                        Download File Below:<br/>
                        <Button variant="primary" @click="saveFile(authorization.file)">
                            Download File
                        </Button>
                    </li>
                    <li>
                        Create a folder "<code>.well-known</code>" in the root folder of your domain. And inside
                        the "<code>.well-known</code>" create another folder "<code>acme-challenge</code>". Then upload
                        the above file(s) inside the acme-challenge folder.
                    </li>
                    <li>
                        Click on the below link(s) and check that it opens a page with random characters on your domain.<br/>
                        Like this:
                        <a :href="`http://${authorization.domain}/.well-known/acme-challenge/${ authorization.file.name}`"
                           target="_blank">
                            http://{{ authorization.domain }}/.well-known/acme-challenge/{{ authorization.file.name }}
                        </a>
                    </li>
                    <li>
                        Click on the button and Let's Encrypt will verify that you own the domain and issue the SSL
                        Certificate (This might take few minutes)<br/>
                        <Button variant="danger" block @click="validateDomain(authorization.challenges[0])">
                            <Spinner v-if="processing" size="sm"/>
                            Verify Domain
                        </Button>
                    </li>
                </ol>
                <!--                <pre>{{ authorization }}</pre>-->
            </div>
            <pre>{{ order }}</pre>
        </Card>
    </Container>
</template>

