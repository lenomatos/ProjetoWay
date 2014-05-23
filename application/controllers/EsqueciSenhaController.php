<?php

class EsqueciSenhaController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    public function solicitarAction()
    {
        $email = $this->_getParam('email');
        
        $modelUsuario = new Application_Model_Usuario();
        
        $usuario = $modelUsuario->select('"US_Email" = \''.$email.'\'');
        
        if ($usuario)
        {
            $token = sha1($usuario[0]['US_Email'] . time());
            $modelAlteracaoSenha = new Application_Model_AlteracaoSenha();
            $modelAlteracaoSenha->solicitar($usuario[0]['US_ID'], $token);
            
            $mensagem = "Clique no link a seguir para alterar a sua senha:<br />";
            $mensagem .= 'http://' . $_SERVER['HTTP_HOST'] . $this->view->baseUrl() . 
                    $this->view->url(array("controller"=>"esqueci-senha","action"=>"redefinir", "token" => $token),null,true);
            
            $assunto = 'Redefinir a senha no Pesca.';
            
            $email = new Application_Model_Email($mensagem, $assunto, $usuario[0]['US_Email'], $usuario[0]['US_Nome']);
            $email->enviar();
            
            $this->view->mensagem = "Uma mensagem com instruções para redefinir a senha foi 
                enviada para seu endereço de e-mail.<br />
                Verifique sua caixa de entrada!";
        }
        else
        {
            $this->view->mensagem = "E-mail não cadastrado.";
        }        
        
    }
        public function redefinirAction()
    {
        $token = $this->_getParam('token');
        
        $modelAlteracaoSenha = new Application_Model_AlteracaoSenha();
        $alteracaoSenha = $modelAlteracaoSenha->find($token);
        
        if ($alteracaoSenha)
        {
            $alteracaoSenha = $alteracaoSenha[0];
                     
                $modelUsuario = new Application_Model_Usuario();
                $usuario = $modelUsuario->find($alteracaoSenha['US_ID']);

                $this->view->login = $usuario['LO_Login'];
                $this->view->token = $token;
            
        }
        else
        {
            $this->view->mensagem = "Token não cadastrado.";
        }
    }
    public function atualizarAction()
    {
        $modelLogin = new Application_Model_Login();
        $modelAlteracaoSenha = new Application_Model_AlteracaoSenha();
        
        $senha1 = sha1($this->_getParam('senha1'));
        $senha2 = sha1($this->_getParam('senha2'));
        
        if ( $senha1 == $senha2 )
        {
            $modelLogin->update($senha1, $this->_getParam('login'));  
            
            $this->view->mensagem = "Senha alterada com sucesso!";
        }
        else
        {
            $this->view->mensagem = "As senhas digitadas não coincidem.";
        }
    }

}

