import apiClient from '../services/api.js';

export const fetcher_api = url => apiClient.get(url).then(res => res.data)