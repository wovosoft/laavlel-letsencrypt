import {reactive, ref, toRef} from "vue";
import axios, {AxiosResponse} from "axios";


export default function (
    data: any = {},
    onSuccess: ((response: AxiosResponse) => any) | any = null,
    onError: ((response: AxiosResponse) => any) | any = null,
) {
    const pluckData = () => {
        // let out = {};
        // Object.keys(data).forEach(key => output[key] = output[key]);
        // return output;

        return output.data;
    }

    const handleOptions = (promise: Promise<AxiosResponse<any, any>>) => {
        if (typeof onSuccess === "function") {
            promise.then(res => onSuccess(res));
        }

        if (typeof onError === "function") {
            promise.catch(err => onError(err));
        }
    }

    const output = reactive({
        data,
        processing: false,
        post(url: string) {
            output.processing = true;
            const promise = axios
                .post(url, pluckData())
                .finally(() => output.processing = false);

            handleOptions(promise);

            return promise;
        },
        put(url: string) {
            output.processing = true;
            const promise = axios
                .put(url, pluckData())
                .finally(() => output.processing = false);

            handleOptions(promise);

            return promise;
        },
        head(url: string) {
            output.processing = true;
            const promise = axios
                .head(url, pluckData())
                .finally(() => output.processing = false);

            handleOptions(promise);

            return promise;
        },
        patch(url: string) {
            output.processing = true;
            const promise = axios
                .patch(url, pluckData())
                .finally(() => output.processing = false);

            handleOptions(promise);

            return promise;
        },
        get(url: string) {
            output.processing = true;
            const promise = axios
                .get(url, pluckData())
                .finally(() => output.processing = false);

            handleOptions(promise);

            return promise;
        },
        delete(url: string) {
            output.processing = true;
            const promise = axios
                .delete(url, pluckData())
                .finally(() => output.processing = false);

            handleOptions(promise);

            return promise;
        },
    })
    return output;
}
