/**
 * @link https://github.com/chartjs/Chart.js/blob/master/src/helpers/helpers.core.ts#L15-L21
 */
import dayjs from "dayjs";
import {computed, ref, UnwrapRef, useModel, watch} from "vue";

export const uid = (() => {
    let id = 0;
    return () => id++;
})();

export const toDateTime = (value: string) => {
    if (!value) {
        return;
    }
    return dayjs(value).format('DD/MM/YYYY hh:mm A');
}

//: Ref<T[K]>
export function useSelectorModel<T extends Record<string, any>, K extends keyof T>(props: T, name: K, initial: T, prop: string, options?: {
    local?: boolean;
}) {
    //modelValue updater
    const model = useModel(props, name, options);

    //internally selected item
    const selectedItem = ref<typeof initial>(initial);
    watch(model, value => {
        if (!value) {
            //@ts-ignore
            selectedItem.value = null;
        }
    });

    return computed({
        get: () => selectedItem.value || null,
        set: (selected: UnwrapRef<typeof initial>) => {
            selectedItem.value = selected;

            //updates modelValue (id)
            model.value = selected?.[prop] || null;
        }
    });
}
