export default function () {
    let url = new URL(window.location.href);
    return url.searchParams;
}
