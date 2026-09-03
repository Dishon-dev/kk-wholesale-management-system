import { reactive, ref } from 'vue';
import { validate as runValidators } from '@/utils/validators';

export function useForm(initialValues, { rules } = {}) {
    const initial = { ...initialValues };
    const data = reactive({ ...initialValues });
    const errors = reactive({});
    const processing = ref(false);

    function reset(overrides = {}) {
        Object.keys(data).forEach((key) => delete data[key]);
        Object.assign(data, initial, overrides);
        clearErrors();
    }

    function clearErrors() {
        Object.keys(errors).forEach((key) => delete errors[key]);
    }

    function setServerErrors(fieldErrors = {}) {
        clearErrors();
        Object.entries(fieldErrors).forEach(([field, messages]) => {
            errors[field] = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    function validateLocally() {
        if (!rules) return true;
        const fieldSpecs = Object.entries(rules).map(([field, fieldRules]) => [
            field,
            data[field],
            ...fieldRules,
        ]);
        const localErrors = runValidators(fieldSpecs);
        Object.assign(errors, localErrors);
        return Object.keys(localErrors).length === 0;
    }

    async function submit(requestFn) {
        clearErrors();
        if (!validateLocally()) {
            return { ok: false };
        }

        processing.value = true;
        try {
            const result = await requestFn({ ...data });
            return { ok: true, result };
        } catch (error) {
            if (error?.fieldErrors && Object.keys(error.fieldErrors).length) {
                setServerErrors(error.fieldErrors);
            }
            return { ok: false, error };
        } finally {
            processing.value = false;
        }
    }
    
    return reactive({ data, errors, processing, reset, submit, clearErrors, setServerErrors });
}
