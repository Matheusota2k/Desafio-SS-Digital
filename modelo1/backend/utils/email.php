<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendActivationEmail($to, $activationLink) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Ajuste conforme seu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'seu-email@gmail.com'; // Seu email
        $mail->Password = 'sua-senha'; // Sua senha
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinatários
        $mail->setFrom('seu-email@gmail.com', 'Sistema de Login');
        $mail->addAddress($to);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = 'Ative sua conta';
        $mail->Body = "
            <h2>Bem-vindo ao Sistema de Login!</h2>
            <p>Para ativar sua conta, clique no link abaixo:</p>
            <p><a href='{$activationLink}'>Ativar minha conta</a></p>
            <p>Se o link não funcionar, copie e cole este endereço no seu navegador:</p>
            <p>{$activationLink}</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
        return false;
    }
}
?>