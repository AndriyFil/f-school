import * as http from './http_service.js'
import jwt from 'jsonwebtoken';
import store from '../store'

export function register(user) {
    return http.http().post('/auth/register', user);
}
export function login(user) {
    return http.http().post('/login', user)
        .then(response => {
            if(response.status === 200) {
                setToken(response.data)
            }
            return response.data
        })
}
export function logout() {
    http.http().get('/auth/logout')
    localStorage.removeItem('larave-vue-token');
}

function setToken(user) {
    const token = jwt.sign({user:user}, 'shhhhhhhhhhh')
    localStorage.setItem('larave-vue-token', token);
    store.dispatch('authenticate', user.user);
}

export function getAccessToken() {
    const token = localStorage.getItem('larave-vue-token');
    if(!token) {
        return null;
    }
    const tokenData = jwt.decode(token);
    return tokenData.user.access_token;
}

export function isLoggedIn() {
    const token = localStorage.getItem('larave-vue-token');
    return token != null;
}

export function getProfile() {
    return http.http().get('/auth/profile')
}
