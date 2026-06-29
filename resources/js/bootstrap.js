import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.request.use(config => {
    if (window.Echo && typeof window.Echo.socketId === 'function') {
        const socketId = window.Echo.socketId();
        if (socketId) {
            config.headers['X-Socket-Id'] = socketId;
        } else {
            delete config.headers['X-Socket-Id'];
        }
    } else {
        delete config.headers['X-Socket-Id'];
    }
    return config;
});