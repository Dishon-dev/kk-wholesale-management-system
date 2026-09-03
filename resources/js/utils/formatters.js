const currencyFormatter = new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    currencyDisplay: "code",
    minimumFractionDigits: 2,
});

const numberFormatter = new Intl.NumberFormat("en-KE");

const dateTimeFormatter = new Intl.DateTimeFormat("en-KE", {
    dateStyle: "medium",
    timeStyle: "short",
});

const dateFormatter = new Intl.DateTimeFormat("en-KE", { dateStyle: "medium" });

export function formatCurrency(value) {
    const amount = Number(value ?? 0);
    return currencyFormatter
        .format(Number.isFinite(amount) ? amount : 0)
        .replace(/\u00A0/g, " ");
}

export function formatNumber(value) {
    const amount = Number(value ?? 0);
    return numberFormatter.format(Number.isFinite(amount) ? amount : 0);
}

export function formatDate(value) {
    if (!value) return "";
    return dateFormatter.format(new Date(value));
}

export function formatDateTime(value) {
    if (!value) return "";
    return dateTimeFormatter.format(new Date(value));
}

export function formatRelativeTime(value) {
    if (!value) return "";

    const diffMs = new Date(value).getTime() - Date.now();
    const diffMinutes = Math.round(diffMs / 60000);
    const rtf = new Intl.RelativeTimeFormat("en", { numeric: "auto" });

    if (Math.abs(diffMinutes) < 60) return rtf.format(diffMinutes, "minute");
    const diffHours = Math.round(diffMinutes / 60);
    if (Math.abs(diffHours) < 24) return rtf.format(diffHours, "hour");
    const diffDays = Math.round(diffHours / 24);
    return rtf.format(diffDays, "day");
}

export function initials(name = "") {
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join("");
}
