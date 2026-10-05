export function debounce(callback, wait = 300) {
    let timer = null;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), wait);
    };
}
