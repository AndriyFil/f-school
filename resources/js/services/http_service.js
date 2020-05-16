import axios from 'axios'
import store from '../store.js'
import * as auth from './auth_service.js'

export function http() {
    return axios.create({
        baseURL: store.state.apiURL
        , headers: {
            Authorization: 'Bearer ' + auth.getAccessToken()
        }
    })
}
export function httpFile() {
    return axios.create({
        baseURL: store.state.apiURL
        , headers: {
            Authorization: 'Bearer ' + auth.getAccessToken()
            , 'Content-Type': 'multipart/form-data'
        }
    })
}

export async function request(url, method = 'GET', data = null) {
    try {
        const headers = {}
        let body

        if (data) {
            headers['Content-Type'] = "application/json"
            body = JSON.stringify(data)
        }

        const response = await fetch (url, {
            method
            , headers
            , body
        })
        return response.json()
    } catch (e) {
        console.warn('Error: ', e.message);
    }
}
