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
      if (mb_strlen($this->nome) < 257 && $this->nome != null) {
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
      $dateObj = DateTime::createFromFormat('Y-m-d', $this->dataNasc);

      if ($dateObj) {
        $hoje = new DateTime();
        $diferenca = $hoje->diff($dateObj);

        return $diferenca->y >= 18 && $diferenca->y <= 130;
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
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, senha, tipo_plano, status) 
      VALUES (:nome, :nick, :dataNasc, :email, :senha, 0, 0)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      $insert->bindParam(':senha', $hash);

      $hash = hash('sha256', $this->senha);
      
      $insert->execute();
    } 

    public function cadastraGoogle($conn) {
      $insert = $conn->prepare(
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, tipo_plano, status) 
      VALUES (:nome, :nick, :dataNasc, :email, 0, 1)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      
      $insert->execute();
    }

    public function cadastraSpotify($conn) {
      $insert = $conn->prepare(
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, tipo_plano, status) 
      VALUES (:nome, :nick, :dataNasc, :email, 0, 0)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      
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

        $senha = $stmt->fetchColumn();
        
        if($senha == null) return false;
        
        if (hash_equals($senha, hash('sha256', $this->senha))) {
          return 1;
        } else {
          return 0;
        }
      } else {
        return 2;
      }
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
    
    public function selectEmail($conn, $idUsuario) {
      $query = "SELECT email FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->email = $row['email'];

      return true;
    }

    public function selectNick($conn) {
      $query = "SELECT username FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      $idUsuario = $stmt->fetchColumn();
      return $idUsuario;
    }

    public function insertCodigo($conn, $idUsuario, $codigo) {
      
      $query = "DELETE FROM Confirmacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $query = "INSERT INTO Confirmacao (id_usuario, codigo) VALUES (:id, :codigo)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->execute();

      return true;
    }

    public function verificacaoEmail($conn, $idUsuario) {
      
      $query = "SELECT status FROM Usuario WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $status = $stmt->fetchColumn();

      if ($status == 1) {
        return true;
      } else {
        return false;
      }
    }

    public function confirmaCodigo($conn, $idUsuario, $codigo) {
      
      $query = "SELECT * FROM Confirmacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row['tentativas'] > 4) {
        return $row['tentativas'];
      }

      if ($row['codigo'] == $codigo) {

        $query = "UPDATE Usuario SET status = 1 WHERE id_usuario = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        $query = "DELETE FROM Confirmacao WHERE id_usuario = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        return 0;
      } else {
        $query = "UPDATE Confirmacao SET tentativas = tentativas + 1 WHERE id_usuario = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        return $row['tentativas'] + 1;
      }
    }

    public function insertCodigoSenha($conn, $idUsuario, $codigo) {
      
      $query = "DELETE FROM Recuperacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $tempo = date('Y-m-d H:i:s');

      $query = "INSERT INTO Recuperacao (id_usuario, codigo, tempo) VALUES (:id, :codigo, :tempo)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->bindParam(':tempo', $tempo);
      $stmt->execute();

      return true;
    }

    public function verificaCodigoSenha($conn, $codigo) {
      
      $query = "SELECT * FROM Recuperacao WHERE codigo = :codigo";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row == null) {
        return 0;
      }

      $tempoLimite = new DateTime($row['tempo']);
      $tempoAtual = new DateTime();

      $tempoLimite->add(new DateInterval('P1D')); 

      if ($tempoLimite < $tempoAtual) {
        $query = "DELETE FROM Recuperacao WHERE codigo = :codigo";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();

        return -1;
      } else {
        return 1;
      } 
    }

    public function trocaSenha($conn, $codigo, $senha) {
      $senha = hash('sha256', $senha);

      $query = "SELECT id_usuario FROM Recuperacao WHERE codigo = :codigo";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->execute();

      $idUsuario = $stmt->fetchColumn();

      $query = "UPDATE Usuario SET senha = :senha WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':senha', $senha);
      $stmt->execute();

      $query = "DELETE FROM Recuperacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();
    }

    public function getPlano($conn, $idUsuario) {
      $query = "SELECT tipo_plano FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      return $row['tipo_plano'];
    }

    private function getEmailCensurado($email) {

      list($nome, $dominio) = explode('@', $email);
      $letra = substr($nome, 0, 1);
      $censurado = $letra . str_repeat('*', strlen($nome) - 1);
      
      return $censurado . '@' . $dominio;
    }

    public function selectLogin($conn) {
      $query = "SELECT id_usuario, username, tipo_plano FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function trocaPlano($conn, $idUsuario, $plano) {
      $query = "UPDATE Usuario SET tipo_plano = :plano WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":plano", $plano);
      $stmt->bindParam(":idUsuario", $idUsuario);
      $stmt->execute();
      
      return $plano;
    }

    public function verificaSenha($conn, $idUsuario, $senha) {
      $query = "SELECT senha FROM Usuario WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":idUsuario", $idUsuario);
      $stmt->execute();
      
      return hash('sha256', $senha) == $stmt->fetchColumn();
    }

    public function novaSenha($conn, $idUsuario) {
      $hash = hash('sha256', $this->senha);

      $query = "UPDATE Usuario SET senha = :novaSenha WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":novaSenha", $hash);
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();
    }

    public function atualiza($conn, $idUsuario, $icon) {
      $query = "UPDATE Usuario SET nome = :nome, username = :username, icon = :icon,data_nasc = :data_nasc WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":nome", $this->nome);
      $stmt->bindParam(":username", $this->nick);
      $stmt->bindParam(":icon", $icon);
      $stmt->bindParam(":data_nasc", $this->dataNasc);
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();
    }

    public function trocaIcone($conn, $idUsuario, $icon) {
      $stmt = $conn->prepare("SELECT icon from Usuario WHERE id_usuario = :idUsuario");
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();

      if($stmt->fetchColumn() == $icon) return false;

      $stmt = $conn->prepare("UPDATE Usuario SET icon = :icon WHERE id_usuario = :idUsuario");
      $stmt->bindParam(":icon", $icon);
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();

      return true;
    }
    
    public function getUsername($conn, $idUsuario) {
      $query = "SELECT username FROM Usuario WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":idUsuario", $idUsuario);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result['username'];
    }

    public function perfil($conn, $idUsuario) {
      $query = "SELECT nome, email, username, data_nasc, tipo_plano, status, icon FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

      $perfil['icon'] = str_replace(' ', '', $perfil['icon']);
      $perfil['email'] = $this->getEmailCensurado($perfil['email']);
      $perfil['data_nasc'] = date("d/m/Y", strtotime($perfil['data_nasc']));

      $perfil['plano'] = $perfil['tipo_plano'];
      unset($perfil['tipo_plano']);

      $perfil['nick'] = $perfil['username'];
      unset($perfil['username']); 
      
      if ($perfil['status'] == null) {
        $perfil['confirmacao'] = false;
      } else {
        $perfil['confirmacao'] = true;
      }
      unset($perfil['status']);

      return $perfil;
    }

    public function delete($conn, $idUsuario) {
      $query = 
        "UPDATE Usuario SET 
          nome = null, 
          username = null, 
          icon = null, 
          data_nasc = null, 
          email = null, 
          senha = null, 
          tipo_plano = null, 
          status = 2 
        WHERE id_usuario = :id and (status <> 2 OR status IS NULL)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      } else {
        return true;
      }
    }

    public function confirmacao($conn, $idUsuario) {
      $query = "SELECT status from Usuario where id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->fetchColumn() != null) {
        return true;
      } else {
        return false;
      }
    }

    public function getFila($conn, $idUsuario) {
      $query = "SELECT id_usuariomusicasala, id_musica from UsuarioMusicaSala where status = 0 and id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalas($conn, $idUsuario) {
      $query = 
        "SELECT
            S.id_sala,
            S.nome,
            (SELECT TOP 1 A.id_musica
            FROM 
                UsuarioMusicaSala A INNER JOIN Sala B ON A.id_sala = B.id_sala
            WHERE 
            dbo.datacorreta() < B.data_criacao + ordem_sala AND A.id_sala = MS.id_sala
            ORDER BY 
                A.ordem_sala) AS id_musica
        FROM
            UsuarioMusicaSala MS
            INNER JOIN Sala S ON MS.id_sala = S.id_sala
        WHERE
            MS.id_usuario = :id and dbo.datacorreta() < DATEADD(DAY,7,S.data_criacao)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistorico($conn, $idUsuario) {
      $stmt = $conn->prepare("declare @i int;
        EXEC SP_RETORNAHISTORICO :id, @i output;
        select @i;");
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $stmt->nextRowset();
      $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->nextRowset();

      return array(
        "total" => $stmt->fetchColumn(),
        "salas" => $salas
      );
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