import axios from 'axios';
import API_CONFIG from '../config/api.config';

const api = axios.create({
    baseURL: API_CONFIG.BASE_URL,
    timeout: API_CONFIG.TIMEOUT,
    headers: API_CONFIG.HEADERS
});

export const login = async (email, password, remember) => {
    try {
        const response = await api.post('/auth/login.php', {
            email,
            password,
            remember
        });
        
        if (response.data.status) {
            const userData = {
                id: response.data.user.id,
                email: response.data.user.email
            };

            if (remember && response.data.token) {
                localStorage.setItem('authToken', response.data.token);
                localStorage.setItem('user', JSON.stringify(userData));
            } else {
                sessionStorage.setItem('user', JSON.stringify(userData));
            }
        }
        
        return response.data;
    } catch (error) {
        throw new Error(error.response?.data?.message || 'Erro ao realizar login');
    }
};

export const register = async (email, password) => {
    try {
        const response = await api.post('/auth/register.php', {
            email,
            password
        });
        return response.data;
    } catch (error) {
        throw new Error(error.response?.data?.message || 'Erro ao realizar cadastro');
    }
};

export const logout = async () => {
    try {
        const token = localStorage.getItem('authToken');
        const response = await api.post('/auth/logout.php', { token });
        
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        sessionStorage.removeItem('user');
        
        return response.data;
    } catch (error) {
        throw new Error(error.response?.data?.message || 'Erro ao realizar logout');
    }
};

export const checkSession = async () => {
    try {
        const response = await api.get('/auth/check_session.php');
        return response.data;
    } catch (error) {
        throw new Error('Sessão inválida');
    }
};

export const verifyToken = async (token) => {
    try {
        const response = await api.post('/auth/verify_token.php', { token });
        return response.data;
    } catch (error) {
        throw new Error('Token inválido');
    }
};