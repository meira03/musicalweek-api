<?php
    class Fila {
        private $idUsuario;
        private $idMusica;
        private $idGenero;
    
        public function __construct($idUsuario, $idMusica, $idGenero) {
            $this->idUsuario = $idUsuario;
            $this->idMusica = $idMusica;
            $this->idGenero = $idGenero;
        }
        
        public function preparaGenero($conn) {
            $select = $conn->prepare(
                "SELECT id_genero FROM [dbo].[Genero] WHERE nome = :nome"
            );
            $select->bindParam(':nome', $this->idGenero);
            $select->execute();
            $id = $select->fetchColumn();

            if ($id != null) {
                $this->idGenero = $id;
                return;
            } else {
                $insert = $conn->prepare(
                    "INSERT INTO [dbo].[Genero] (nome) VALUES (:nome)"
                );
                $insert->bindParam(':nome', $this->idGenero);
                $insert->execute();
                
                $select = $conn->prepare(
                    "SELECT id_genero FROM [dbo].[Genero] WHERE nome = :nome"
                );
                $select->bindParam(':nome', $this->idGenero);
                $select->execute();
                $this->idGenero = $select->fetchColumn();
            }
        }

        public function insere($conn) {

            $this->preparaGenero($conn);

            $insert = $conn->prepare(
            "INSERT INTO [dbo].[MusicaSala] (id_usuario, id_musica, id_genero, data_adicao_musica, usuario_status) 
            VALUES (:usuario, :musica, :genero, :dataadicao, 0)"
            );

            $insert->bindParam(':usuario', $this->idUsuario);
            $insert->bindParam(':musica', $this->idMusica);
            $insert->bindParam(':genero', $this->idGenero);
            $insert->bindParam(':dataadicao', $data);

            $data = date('Y-m-d H:i:s');
            
            $insert->execute();

            $select = $conn->prepare(
                "SELECT top 1 id_musicasala from MusicaSala where id_usuario = :usuario order by id_musicasala desc"
            );
            $select->bindParam(':usuario', $this->idUsuario);
            $select->execute();
            $id = $select->fetchColumn();

            return $id;
        }

        public function getIdUsuario() {
            return $this->idUsuario;
        }
    
        public function getIdMusica() {
            return $this->idMusica;
        }
    
        public function getIdGenero() {
            return $this->idGenero;
        }
    }
?>