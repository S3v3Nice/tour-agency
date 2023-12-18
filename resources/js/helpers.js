export function changeTitle(title) {
    document.title = title + ' - ' + import.meta.env.VITE_APP_NAME
}

export function getAbsolutePath(path) {
    return `/${path}`
}

export function isEmptyObject(data) {
    return Object.keys(data).length === 0;
}

export function formatDateTime(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}