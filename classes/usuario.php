<?php
  class Usuario {
    private $nome;
    private $nick;
    private $dataNasc;
    private $email;
    private $senha; 

    public function __construct($nome, $nick, $dataNasc, $email, $senha) {
      
      $this->nome = $nome;
      $this->nick = $nick;
      $this->dataNasc = $dataNasc;
      $this->email = $email;
      $this->senha = $senha;
    }

    public function validarNome() {
      if (mb_strlen($this->nome) < 257) {
        return true; 
      } else {
        return false; 
      }
    }

    public function validarNick() {
      $quantidade = mb_strlen($this->nick);
      if (3 < $quantidade && $quantidade < 17) {
        return true; 
      } else {
        return false; 
      }
    }

    public function validarData() {
      $formato = 'Y-m-d'; 

      
      $dateObj = DateTime::createFromFormat($formato, $this->dataNasc);
  
      
      if ($dateObj && $dateObj->format($formato) === $this->dataNasc) {
        return true;
      } else {
        return false;
      }
    }

    public function validarEmail() {
      $padrao = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

      if (preg_match($padrao, $this->email)) {
        return true; 
      } else {
        return false; 
      }
    }

    public function validarSenha() {
      
      $tamanhoMinimo = 8; 
      $exigeMaiuscula = true; 
      $exigeMinuscula = true; 
      $exigeNumero = true; 
      $exigeCaractereEspecial = true;
  
      if (mb_strlen($this->senha) < $tamanhoMinimo) {
        return false; 
      }
  
      $valido = true;
  
      if ($exigeMaiuscula && !preg_match('/[A-Z]/', $this->senha)) {
        $valido = false;
      }
  
      if ($exigeMinuscula && !preg_match('/[a-z]/', $this->senha)) {
        $valido = false;
      }
  
      if ($exigeNumero && !preg_match('/[0-9]/', $this->senha)) {
        $valido = false;
      }
  
      if ($exigeCaractereEspecial && !preg_match('/[^a-zA-Z0-9]/', $this->senha)) {
        $valido = false;
      }
  
      return $valido;
    }

    public function verificaEmail($conn) {
      $query = "SELECT COUNT(*) AS total_email FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      $result = $stmt->fetchColumn();
      if (0 == $result) {
        return true;
      } else {
        return false;
      }
    }

    public function verificaNick($conn) {
      $query = "SELECT COUNT(*) AS total_user_nome FROM [dbo].[Usuario] WHERE username = :nick";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":nick", $this->nick);
      $stmt->execute();
      $result = $stmt->fetchColumn();
      if (0 == $result) {
        return true;
      } else {
        return false;
      }
    }

    public function cadastra($conn) {
      $insert = $conn->prepare(
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, senha, tipo_plano) 
      VALUES (:nome, :nick, :dataNasc, :email, :senha, 1)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      $insert->bindParam(':senha', $hash);

      $hash = password_hash($this->senha, PASSWORD_DEFAULT);
      
      $insert->execute();
    }

    public function login($conn) {
      $stmt = $conn->prepare("SELECT COUNT(*) AS total_email FROM [dbo].[Usuario] WHERE email = :email");
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      if (1 == $stmt->fetchColumn()) {
        $stmt = $conn->prepare("SELECT senha FROM [dbo].[Usuario] WHERE email = :email");
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        if (password_verify($this->senha, $stmt->fetchColumn())) {
          return true;
        }
      }
      return false;
    }

    public function getid($conn) {
      $query = "SELECT id_usuario FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      $idUsuario = $stmt->fetchColumn();
      return $idUsuario;
    }

    public function select($conn, $idUsuario) {
      $query = "SELECT nome, email, username, data_nasc FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->nome = $row['nome'];
      $this->email = $row['email'];
      $this->nick = $row['username'];
      $this->dataNasc = $row['data_nasc'];

      return true;
    }
    
    public function getNome() {
        return $this->nome;
    }

    public function getNick() {
        return $this->nick;
    }

    public function getDataNasc() {
        return $this->dataNasc;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getSenha() {
        return $this->senha;
    }
  }
?> 