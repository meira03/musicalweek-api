<?php
    class Avaliacao {
        private $idUsuario;
        private $idSala;
        private $nota;
    
        public function __construct($idUsuario, $idSala, $nota) {
            $this->idUsuario = $idUsuario;
            $this->idSala = $idSala;
            $this->nota = $nota;
        }

        public function insere ($conn) {
            $insert =  $conn->prepare("SP_INSERE_NOTA :usuario, :musicasala, :nota");
            $insert->bindParam(":usuario", $this->idUsuario);
            $insert->bindParam(":musicasala", $this->idSala);
            $insert->bindParam(":nota", $this->nota);
            $insert->execute();

            return $insert->fetchColumn();
        }

        public function validaUsuarioSala($conn) {
            $query = "SELECT COUNT(*) FROM [dbo].[MusicaSala] WHERE id_usuario = :usuario 
            AND id_sala = (SELECT id_sala FROM [dbo].[MusicaSala] WHERE id_musicasala = :sala)";
            $select = $conn->prepare($query);
            $select->bindParam(":usuario", $this->idUsuario);
            $select->bindParam(":sala", $this->idSala);
            $select->execute();

            $count = $select->fetchColumn();

            if ($count > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function validaChave($conn) {
            $query = "SELECT COUNT(*) FROM [dbo].[Avaliacao] WHERE id_usuario = :usuario AND id_musicasala = :sala";
            $select = $conn->prepare($query);
            $select->bindParam(":usuario", $this->idUsuario);
            $select->bindParam(":sala", $this->idSala);
            $select->execute();

            $count = $select->fetchColumn();

            if ($count > 0) {
                return false;
            } else {
                return true;
            }
        }

        public function registra($conn) {

            if (!$this->validaChave($conn)) {
                return false;
            }

            $query = "INSERT INTO [dbo].[Avaliacao] (id_usuario, id_musicasala, nota, data_avaliacao) 
            VALUES (:usuario, :sala, :nota, :dataavaliacao)";

            $insert = $conn->prepare($query);

            $insert->bindParam(":usuario", $this->idUsuario);
            $insert->bindParam(":sala", $this->idSala);
            $insert->bindParam(":nota", $this->nota);
            $insert->bindParam(":dataavaliacao", $this->data);

            $insert->execute();
            
            return true;
        }
    
        public function getIdUsuario() {
            return $this->idUsuario;
        }
    
        public function getIdSala() {
            return $this->idSala;
        }
    
        public function getNota() {
            return $this->nota;
        }
    
        public function getData() {
            return $this->data;
        }
    }
?>