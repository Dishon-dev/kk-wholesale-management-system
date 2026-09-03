export function required(value) {
    if (value === null || value === undefined) return 'This field is required.';
    if (typeof value === 'string' && value.trim() === '') return 'This field is required.';
    return null;
}

export function isEmail(value) {
    if (!value) return null;
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(value) ? null : 'Enter a valid email address.';
}

export function minLength(min) {
    return (value) => {
        if (!value) return null;
        return String(value).length >= min ? null : `Must be at least ${min} characters.`;
    };
}

export function isPositiveNumber(value) {
    if (value === null || value === undefined || value === '') return null;
    return Number(value) > 0 ? null : 'Must be greater than zero.';
}

export function isNonNegativeNumber(value) {
    if (value === null || value === undefined || value === '') return null;
    return Number(value) >= 0 ? null : 'Cannot be negative.';
}

export function validate(fields) {
    const errors = {};
    for (const [field, value, ...rules] of fields) {
        for (const rule of rules) {
            const message = rule(value);
            if (message) {
                errors[field] = message;
                break;
            }
        }
    }
    return errors;
}
