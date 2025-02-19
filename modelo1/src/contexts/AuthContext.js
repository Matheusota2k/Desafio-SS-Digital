import React, { createContext, useState, useContext, useEffect } from 'react';
import { checkSession } from '../services/auth';

const AuthContext = createContext({});

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadStoredAuth = async () => {
            try {
                const storedUser = localStorage.getItem('user') || sessionStorage.getItem('user');
                if (storedUser) {
                    const userData = JSON.parse(storedUser);
                    const sessionResponse = await checkSession();
                    if (sessionResponse.status) {
                        setUser(userData);
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar autenticação:', error);
            } finally {
                setLoading(false);
            }
        };

        loadStoredAuth();
    }, []);

    return (
        <AuthContext.Provider value={{ user, setUser, loading }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth deve ser usado dentro de um AuthProvider');
    }
    return context;
};