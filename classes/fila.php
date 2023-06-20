<?php
    class Fila {
        private $idUsuario;
        private $idMusica;
    
        public function __construct($idUsuario, $idMusica) {
            $this->idUsuario = $idUsuario;
            $this->idMusica = $idMusica;
        }

        public function insere($conn) {

            $insert = $conn->prepare(
            "INSERT INTO [dbo].[MusicaSala] (id_usuario, id_musica, data_adicao_musica, id_genero, usuario_status) 
            VALUES (:usuario, :musica, :dataadicao, 605, 0)"
            );

            $insert->bindParam(':usuario', $this->idUsuario);
            $insert->bindParam(':musica', $this->idMusica);
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
    }
?>