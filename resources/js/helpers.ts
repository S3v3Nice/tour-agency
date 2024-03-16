interface ImportMeta {
    env: {
        VITE_APP_NAME: string
    }
}

export function changeTitle(title: string) {
    document.title = title + ' - ' + import.meta.env.VITE_APP_NAME
}

export function getAbsolutePath(path: string) {
    return `/${path}`
}

export function isEmptyObject(data: object) {
    return Object.keys(data).length === 0
}

export function formatDateTime(dateString: string) {
    const options = {year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric'}
    return new Date(dateString).toLocaleDateString(undefined, options)
}

export function getErrorMessageByCode(code: number) {
    switch (code) {
        case 401:
            return 'Требуется авторизация для совершения данного действия.'
        case 403:
            return 'У вас нет доступа для совершения данного действия.'
        case 429:
            return 'Слишком много запросов. Повторите позже.'
        default:
            return 'Неизвестная ошибка'
    }
}
