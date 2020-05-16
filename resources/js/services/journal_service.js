import * as http from './http_service.js'

// export function getSubjects (id) {
//     return http.http().get('/subjects/'+id)
//         .then(response => { return response.data
//     })
// }
export async function getSubjects (id) {
   return await http.request('/api/subjects/'+id)
}
