import {http, httpFile} from './http_services.js'

export function register(user) {
    return http().post('/auth/register', user);
}
