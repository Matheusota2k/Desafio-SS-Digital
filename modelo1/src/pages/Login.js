import React, { useState, useEffect } from 'react';
import { 
  Box, 
  TextField, 
  Button, 
  Container, 
  Typography, 
  FormControlLabel,
  Checkbox,
  Alert,
  Link
} from '@mui/material';
import { useNavigate } from 'react-router-dom';
import { login, verifyToken } from '../services/auth';
import { useAuth } from '../contexts/AuthContext';

function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();
  const { setUser } = useAuth();

  useEffect(() => {
    const checkToken = async () => {
      const token = localStorage.getItem('authToken');
      if (token) {
        try {
          const response = await verifyToken(token);
          if (response.status) {
            navigate('/dashboard');
          } else {
            localStorage.removeItem('authToken');
          }
        } catch (err) {
          localStorage.removeItem('authToken');
        }
      }
    };

    checkToken();
  }, [navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    try {
      const response = await login(email, password, rememberMe);

      if (response.status) {
        setSuccess('Login realizado com sucesso!');
        setUser(response.user);
        
        setTimeout(() => {
          navigate('/dashboard');
        }, 1000);
      } else {
        setError(response.message);
      }
    } catch (err) {
      setError(err.message || 'Erro ao conectar com o servidor');
    }
  };

  return (
    <Container component="main" maxWidth="xs">
      <Box
        sx={{
          marginTop: 8,
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          padding: 3,
          borderRadius: 2,
          boxShadow: 3,
          backgroundColor: 'white'
        }}
      >
        <Typography component="h1" variant="h5" sx={{ mb: 3 }}>
          Login
        </Typography>

        {error && (
          <Alert severity="error" sx={{ width: '100%', mb: 2 }}>
            {error}
          </Alert>
        )}

        {success && (
          <Alert severity="success" sx={{ width: '100%', mb: 2 }}>
            {success}
          </Alert>
        )}

        <Box component="form" onSubmit={handleSubmit} sx={{ mt: 1, width: '100%' }}>
          <TextField
            margin="normal"
            required
            fullWidth
            id="email"
            label="Email"
            name="email"
            autoComplete="email"
            autoFocus
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            sx={{ mb: 2 }}
          />
          <TextField
            margin="normal"
            required
            fullWidth
            name="password"
            label="Senha"
            type="password"
            id="password"
            autoComplete="current-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            sx={{ mb: 2 }}
          />
          <FormControlLabel
            control={
              <Checkbox 
                value="remember" 
                color="primary" 
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
              />
            }
            label="Manter conectado"
            sx={{ mb: 2 }}
          />
          <Button
            type="submit"
            fullWidth
            variant="contained"
            sx={{ mb: 2 }}
          >
            Entrar
          </Button>
          <Link href="/register" variant="body2">
            {"Não tem uma conta? Cadastre-se"}
          </Link>
        </Box>
      </Box>
    </Container>
  );
}

export default Login;