import axios from 'axios'
import store from '../store.js'

export function http() {
    return axios.create({
        baseURL: store.state.apiURL
    })
}
export function httpFile() {
    return axios.create({
        baseURL: store.state.apiURL
        , headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
}
