import { ref } from 'vue';

export interface FormSchema {
    [key: string]: {
        required?: boolean;
        label?: string;
    };
}

export function useForm<T extends Record<string, any>>(schema: FormSchema) {
    if (!schema) {
        throw new Error('Schema is required');
    }

    const values = ref<T>({} as T);
    const errors = ref<Record<string, string>>({});
    const isSubmitting = ref(false);

    function validate() {
        const result: Record<string, string> = {};

        for (const field in schema) {
            const rules = schema[field];
            const value = values.value[field];

            if (rules.required && !value) {
                result[field] = `${rules.label || field} is required`;
                continue;
            }
        }

        errors.value = result;
        return Object.keys(result).length === 0;
    }

    return {
        values,
        errors,
        isSubmitting,
        validate,
    };
}
