<?php
    class Sala {
    
        public function __construct() {
        }

        public function limite($conn, $idUsuario) {
            $stmt = $conn->prepare(
                "SELECT SUM(total_registros) AS total_geral
                FROM (
                    SELECT COUNT(*) AS total_registros
                    FROM MusicaSala MS
                    INNER JOIN Sala S ON MS.id_sala = S.id_sala
                    WHERE MS.id_usuario = :id AND dbo.datacorreta() < DATEADD(DAY, 7, S.data_inicio)
                
                    UNION ALL
                
                    SELECT COUNT(*) AS total_registros
                    FROM MusicaSala
                    WHERE usuario_status = 0 AND id_usuario = :idu
                ) AS subquery;
                
                SELECT tipo_plano from Usuario where id_usuario = :idus"
            );

            $stmt->bindParam(':id', $idUsuario);
            $stmt->bindParam(':idu', $idUsuario);
            $stmt->bindParam(':idus', $idUsuario);
            $stmt->execute();

            $total = $stmt->fetchColumn();

            $stmt->nextRowset();

            $plano = $stmt->fetchColumn();

            if ($plano == 0 && $total > 1) {
                return 2;
            }

            if ($plano == 1 && $total > 4) {
                return 5;
            }

            if ($plano == 2 && $total > 29) {
                return 30;
            }

            return 0;
        }

        public function insereFila($conn, $idUsuario, $idMusica) {
            $insert = $conn->prepare(
                "INSERT INTO [dbo].[MusicaSala] (id_usuario, id_musica, data_adicao_musica, usuario_status) 
                VALUES (:usuario, :musica, dbo.datacorreta(), 0);"
            );

            $insert->bindParam(':usuario', $idUsuario);
            $insert->bindParam(':musica', $idMusica);
            $insert->execute();

            $idMusicaSala = $conn->lastInsertId();

            $select = $conn->prepare(
                "SELECT id_sala from MusicaSala where id_musicasala = :id"
            );
            $select->bindParam(':id', $idMusicaSala);
            $select->execute();

            return array(
                "id_musicasala" => $idMusicaSala, 
                "id_sala" => $select->fetchColumn()
            );
        }

        public function saiFila($conn, $idUsuario, $idMusicaSala) {
            $stmt = $conn->prepare("SP_SAIDAFILA :idmusicasala, :usuario");

            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':idmusicasala', $idMusicaSala);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function buscaSalas($conn, $idUsuario) {
            $query = 
            "SELECT
            MS.id_musicasala AS id_musica_sala,
            S.nome AS nome_sala,
            isnull((SELECT TOP 1 A.id_musica
             FROM 
                MusicaSala A INNER JOIN Sala B ON A.id_sala = B.id_sala
             WHERE 
             :dataatual < B.data_criacao + ordem_sala AND A.id_sala = MS.id_sala
             ORDER BY 
                A.ordem_sala),(select TOP 1 A.id_musica
             FROM 
                MusicaSala A WHERE 
                A.id_musicasala = MS.id_musicasala)) AS id_musica
        FROM
            MusicaSala MS
            LEFT JOIN Sala S ON MS.id_sala = S.id_sala
        WHERE
            MS.id_usuario = :id_usuario";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_usuario', $idUsuario);
            $stmt->bindParam(':dataatual', $dataAtual);
            $dataAtual = date("Y-m-d H:i:s");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        public function verifica($conn, $idMusicaSala) {
            $stmt = $conn->prepare('SELECT id_sala, id_usuario, id_musica, data_adicao_musica FROM MusicaSala WHERE id_musicasala = :id_musicasala');
            $stmt->bindParam(':id_musicasala', $idMusicaSala);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function getFila($conn, $idMusicaSala) {
            $stmt = $conn->prepare(
                'SELECT MS.id_sala AS sala, MS.id_musica AS musica
                FROM MusicaSala MS
                WHERE MS.id_musicasala = :id_musicasala;');
            $stmt->bindParam(':id_musicasala', $idMusicaSala);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        }

        public function getInfo($conn, $idMusicaSala, $idSala, $idUsuario) {
            $stmt = $conn->prepare('SP_STATUS_SALA :idmusicasala');
            $stmt->bindParam(':idmusicasala', $idMusicaSala);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                "sala" => $result["sala"],
                "tempo_restante" => $result["tempo_restante"],
                "sala_finalizada" => $result["sala_finalizada"] == 1,
                "participantes" => $this->getParticipantes($conn, $idSala),
                "musicas" => $this->getMusicas($conn, $idUsuario, $idSala)
            ];
        }

        private function getParticipantes($conn, $idSala) {
            $stmt = $conn->prepare(
                "SELECT B.username AS nick, B.icon
                FROM MusicaSala A
                INNER JOIN Usuario B ON A.id_usuario = B.id_usuario
                WHERE A.id_sala = :sala");

            $stmt->bindParam(':sala', $idSala);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $key => $row) {
                $result[$key]['icon'] = str_replace(' ', '', $row['icon']);
            }
            
            return $result;
        }

        private function getMusicas($conn, $idUsuario, $idSala) {
            $stmt = $conn->prepare(
                "SELECT B.id_musicasala from Sala A
                INNER JOIN MusicaSala B on A.id_sala = B.id_sala
                where B.id_sala = :sala order by ordem_sala");
            $stmt->bindParam(':sala', $idSala);
            $stmt->execute();

            $idsMusicaSala = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare(
                "SELECT top 1 A.id_musicasala from MusicaSala A
                INNER JOIN Sala B on A.id_sala = B.id_sala
                where dbo.datacorreta() < B.data_criacao + ordem_sala
                and A.id_sala = :sala order by ordem_sala");
            $stmt->bindParam(':sala', $idSala);
            $stmt->execute();

            $musicaAtual = $stmt->fetch(PDO::FETCH_COLUMN);

            $musicas = array();

            foreach ($idsMusicaSala as $row) {
                $musicaId = $row['id_musicasala'];
                $stmt = $conn->prepare(
                    "SELECT A.id_musica, A.nota_calculada, B.nota from MusicaSala A
                    LEFT JOIN Avaliacao B ON A.id_musicasala = B.id_musicasala 
                    AND B.id_usuario = :usuario
                    where A.id_musicasala = :musicaid");
                $stmt->bindParam(':usuario', $idUsuario);
                $stmt->bindParam(':musicaid', $musicaId);

                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                array_push($musicas, [
                    "id_musica_sala" => $musicaId,
                    "musica" => $result["id_musica"],
                    "avaliacao_media" =>  $result["nota_calculada"],
                    "nota_usuario" => $result["nota"],
                    "avaliacoes" => $this->getAvaliacoes($conn, $musicaId, $idSala)
                ]);

                if ($musicaAtual == $musicaId) break;
            }

            return $musicas;
        }

        private function getMedia($conn, $musicaId) {
            $Avaliacoes = $this->getAvaliacoes($conn, $musicaId);

            if ($Avaliacoes == null) {
                return null;
            }

            $total = 0;
            $soma = 0;

            foreach ($Avaliacoes as $Avaliacao) {
                $soma += $Avaliacao['nota'];
                $total++;
            }

            return round($soma / $total, 2);
        }

        private function getAvaliacoes($conn, $musicaId, $idSala) {
            $stmt = $conn->prepare(
                "SELECT U.username AS nick, A.nota
                FROM MusicaSala MS
                LEFT JOIN Avaliacao A ON MS.id_musicasala = A.id_musicasala
                LEFT JOIN Usuario U ON A.id_usuario = U.id_usuario
                WHERE MS.id_musica = (select id_musica from MusicaSala where id_musicasala = :musicaid)
                and MS.id_sala = :sala");
            $stmt->bindParam(':musicaid', $musicaId);
            $stmt->bindParam(':sala', $idSala);

            $stmt->execute();

            $resposta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(empty($resposta) || $resposta[0]['nick'] == null) {
                return null;
            } else {
                return $resposta;
            }
        }

        public function selectIdMusicaSala($conn, $sala, $idUsuario) {
            $stmt = $conn->prepare(
                "SELECT id_musicasala from MusicaSala where id_usuario = :usuario and id_sala = :sala");
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':sala', $sala);

            $stmt->execute();

            return $stmt->fetchColumn();
        }
    } 
?>