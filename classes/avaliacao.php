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

        public function insere($conn) {
            $insert =  $conn->prepare("SP_INSERE_NOTA :usuario, :musicasala, :nota");
            $insert->bindParam(":usuario", $this->idUsuario);
            $insert->bindParam(":musicasala", $this->idSala);
            $insert->bindParam(":nota", $this->nota);
            $insert->execute();

            return $insert->fetchColumn();
        }

        public function avaliacaoMedia($conn) {
            $select =  $conn->prepare("SELECT nota_calculada from MusicaSala where id_musicasala = :musicasala");
            $select->bindParam(":musicasala", $this->idSala);
            $select->execute();

            return $select->fetchColumn();
        }

        public function topMusicas($conn) {
            $select =  $conn->prepare("SELECT top 10 id_musica from MusicaSala");
            $select->execute();

            return array_column($select->fetchAll(PDO::FETCH_ASSOC), 'id_musica');
        }
    }
?>