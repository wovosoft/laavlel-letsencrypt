import {ref} from "vue";
// @ts-ignore
import type {ColorVariants} from "@wovosoft/wovoui/dist/types";
import {uid} from "@/Composables/useHelpers";


type ToastType = {
    variant?: ColorVariants
    message?: string
    title?: string
    key: string | number
};
export const toasts = ref<ToastType[]>([]);
export const addToast = (toast: ToastType) => {
    const key = uid();
    toasts.value.push({
        ...toast,
        key
    });
    setTimeout(() => {
        if (toasts.value.findIndex(toast => toast.key === key) > -1) {
            removeToast(key);
        }
    }, 3000)
};
export const removeToast = (key: string | number) => {
    const index = toasts.value.findIndex(i => i.key === key);
    toasts.value.splice(index, 1);
}
