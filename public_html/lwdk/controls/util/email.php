<?php
/*
    VER: 1.0
    LAST-UPDATE: 17/03/2021
*/
function ctrl_util_email($args){
    $instance = new class extends APPControls {
        function initMailer(){
            // Inicia a classe PHPMailer
            $mail = $this->loadPlugin("PHPMailer-5.2-stable@PHPMailer");

            // return ($this->email = $mail);

            // Define os dados do servidor e tipo de conexão
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
            $mail->IsSMTP(); // Define que a mensagem será SMTP
            $mail->Host = "smtp.uni5.net"; // Endereço do servidor SMTP (caso queira utilizar a autenticação, utilize o host smtp.seudomínio.com.br)
            $mail->SMTPAuth = true; // Usar autenticação SMTP (obrigatório para smtp.seudomínio.com.br)
            $mail->Username = "envia@hetsi.com.br"; // Usuário do servidor SMTP (endereço de email)
            $mail->Password = '102030dw'; // Senha do servidor SMTP (senha do email usado)

            // Define o remetente
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
            $mail->Sender = "envia@hetsi.com.br"; // Seu e-mail


            // Define os dados técnicos da Mensagem
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
            $mail->IsHTML(true); // Define que o e-mail será enviado como HTML
            //$mail->CharSet = 'iso-8859-1'; // Charset da mensagem (opcional)
            $mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)

            return($this->email = $mail);
        }

        function from(String $form_name, String $from_email){
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
            $this->email->From = $from_email; // Seu e-mail
            $this->email->FromName = $form_name; // Seu nome
        }

        function add(String $email){
            $this->email->AddAddress($email, $email);
        }

        function addFile(String $file){
            $this->email->AddAttachment($file);
        }

        function send(String $title, String $content){
            $this->email->Subject  = utf8_decode($title); // Assunto da mensagem
            $this->email->Body = $content;
            $this->email->AltBody = ' ';
            $this->email->Send();

			return !(strlen($this->email->ErrorInfo) > 0);
        }
    };

    $instance->args = $args;
    $instance->initMailer();

    return $instance;
}
