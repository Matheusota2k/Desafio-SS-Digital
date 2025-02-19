import React, { useState } from 'react';
import { 
  Box, 
  TextField, 
  Button, 
  Container, 
  Typography, 
  Alert,
  Link
} from '@mui/material';
import { useNavigate } from 'react-router-dom';
import { register } from '../services/auth';

function Register() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    // Validação básica
    if (!email || !password || !confirmPassword) {
      setError('Todos os campos são obrigatórios');
      return;
    }

    // Validar formato do email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      setError('Por favor, insira um email válido');
      return;
    }

    // Validar senha
    if (password.length < 6) {
      setError('A senha deve ter pelo menos 6 caracteres');
      return;
    }

    // Verificar se as senhas coincidem
    if (password !== confirmPassword) {
      setError('As senhas não coincidem');
      return;
    }

    try {
      const response = await register(email, password);

      if (response.status) {
        setSuccess('Cadastro realizado com sucesso!');
        setEmail('');
        setPassword('');
        setConfirmPassword('');
        
        setTimeout(() => {
          navigate('/login');
        }, 2000);
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
          backgroundColor: 'white',
          padding: 3,
          borderRadius: 2,
          boxShadow: 3
        }}
      >
        <Typography component="h1" variant="h5" sx={{ mb: 3 }}>
          Criar Conta
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
            autoComplete="new-password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            sx={{ mb: 2 }}
          />
          <TextField
            margin="normal"
            required
            fullWidth
            name="confirmPassword"
            label="Confirmar Senha"
            type="password"
            id="confirmPassword"
            autoComplete="new-password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            sx={{ mb: 2 }}
          />
          <Button
            type="submit"
            fullWidth
            variant="contained"
            sx={{ mb: 2 }}
          >
            Cadastrar
          </Button>
          <Link href="/login" variant="body2">
            {"Já tem uma conta? Faça login"}
          </Link>
        </Box>
      </Box>
    </Container>
  );
}

export default Register;