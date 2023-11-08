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
            $select =  $conn->prepare("SELECT nota_calculada from UsuarioMusicaSala where id_usuariomusicasala = :musicasala");
            $select->bindParam(":musicasala", $this->idSala);
            $select->execute();

            return $select->fetchColumn();
        }

        public function topMusicas($conn) {
            $select =  $conn->prepare("SELECT top 21 id_musica, nota, periodo from Classificacao order by data_carga desc");
            $select->execute();

            $periodo0 = [];
            $periodo1 = [];
            $periodo2 = [];

            foreach ($select as $row) {
                if ($row[2] == 0) {
                    $periodo0[] = ["id_musica" => $row[0], "nota" => $row[1]];
                } elseif ($row[2] == 1) {
                    $periodo1[] = ["id_musica" => $row[0], "nota" => $row[1]];
                } elseif ($row[2] == 2) {
                    $periodo2[] = ["id_musica" => $row[0], "nota" => $row[1]];
                }
            }

            usort($periodo0, function ($a, $b) {
                return $b["nota"] <=> $a["nota"];
            });

            usort($periodo1, function ($a, $b) {
                return $b["nota"] <=> $a["nota"];
            });

            usort($periodo2, function ($a, $b) {
                return $b["nota"] <=> $a["nota"];
            });

            return [
                array_column(array_slice($periodo0, 0, 7), 'id_musica'),
                array_column(array_slice($periodo1, 0, 7), 'id_musica'),
                array_column(array_slice($periodo2, 0, 7), 'id_musica')
            ];
        }
    }
?>