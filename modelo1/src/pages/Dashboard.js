import React from 'react';
import { Container, Typography, Box, Button } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { logout } from '../services/auth';

function Dashboard() {
    const { user, setUser } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        try {
            await logout();
            setUser(null);
            navigate('/');
        } catch (error) {
            console.error('Erro ao fazer logout:', error);
        }
    };

    return (
        <Container>
            <Box sx={{ mt: 4, p: 3, borderRadius: 2, boxShadow: 3, bgcolor: 'white' }}>
                <Typography variant="h4" gutterBottom>
                    Dashboard
                </Typography>
                <Typography variant="h6" gutterBottom>
                    Bem-vindo, {user?.email}
                </Typography>
                <Button 
                    variant="contained" 
                    color="primary" 
                    onClick={handleLogout}
                    sx={{ mt: 2 }}
                >
                    Logout
                </Button>
            </Box>
        </Container>
    );
}

export default Dashboard;