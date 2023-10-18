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
                    WHERE MS.id_usuario = :id AND dbo.datacorreta() < DATEADD(DAY, 7, S.data_criacao)
                    and S.tipo_sala = 1
                
                    UNION ALL
                
                    SELECT COUNT(*) AS total_registros
                    FROM MusicaSala
                    WHERE status = 0 AND id_usuario = :idu
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
                "INSERT INTO [dbo].[MusicaSala] (id_usuario, id_musica, data_entrada, status) 
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

        // public function buscaSalas($conn, $idUsuario) {
        //     $query = 
        //     "SELECT
        //     MS.id_musicasala AS id_musica_sala,
        //     S.nome AS nome_sala,
        //     isnull((SELECT TOP 1 A.id_musica
        //      FROM 
        //         MusicaSala A INNER JOIN Sala B ON A.id_sala = B.id_sala
        //      WHERE 
        //      :dataatual < B.data_criacao + ordem_sala AND A.id_sala = MS.id_sala
        //      ORDER BY 
        //         A.ordem_sala),(select TOP 1 A.id_musica
        //      FROM 
        //         MusicaSala A WHERE 
        //         A.id_musicasala = MS.id_musicasala)) AS id_musica
        // FROM
        //     MusicaSala MS
        //     LEFT JOIN Sala S ON MS.id_sala = S.id_sala
        // WHERE
        //     MS.id_usuario = :id_usuario and
        //     S.tipo_sala = 1";
        //     $stmt = $conn->prepare($query);
        //     $stmt->bindParam(':id_usuario', $idUsuario);
        //     $stmt->bindParam(':dataatual', $dataAtual);
        //     $dataAtual = date("Y-m-d H:i:s");
        //     $stmt->execute();

        //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
        // }
        
        public function verifica($conn, $idMusicaSala) {
            $stmt = $conn->prepare('SELECT id_sala, id_usuario, id_musica, data_entrada FROM MusicaSala WHERE id_musicasala = :id_musicasala;
            EXEC SP_ESTIMA_TEMPO');
            $stmt->bindParam(':id_musicasala', $idMusicaSala);
            $stmt->execute();

            $select = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();

            return [
                "id_sala" => $select["id_sala"],
                "id_usuario" => $select["id_usuario"],
                "id_musica" => $select["id_musica"],
                "data_entrada" => $select["data_entrada"],
                "tempo_estimado" => $stmt->fetchColumn()
            ];
        }

        // public function getFila($conn, $idMusicaSala) {
        //     $stmt = $conn->prepare(
        //         'SELECT MS.id_sala AS sala, MS.id_musica AS musica
        //         FROM MusicaSala MS
        //         WHERE MS.id_musicasala = :id_musicasala;');
        //     $stmt->bindParam(':id_musicasala', $idMusicaSala);
        //     $stmt->execute();
            
        //     return $stmt->fetch(PDO::FETCH_ASSOC); 
        // }

        public function getInfo($conn, $idMusicaSala, $idSala, $idUsuario) {
            $stmt = $conn->prepare('SP_STATUS_SALA :idmusicasala');
            $stmt->bindParam(':idmusicasala', $idMusicaSala);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result["tipo_sala"] != 1) return ["tipo_sala" => $result["tipo_sala"]];

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

        // private function getMedia($conn, $musicaId) {
        //     $Avaliacoes = $this->getAvaliacoes($conn, $musicaId);

        //     if ($Avaliacoes == null) {
        //         return null;
        //     }

        //     $total = 0;
        //     $soma = 0;

        //     foreach ($Avaliacoes as $Avaliacao) {
        //         $soma += $Avaliacao['nota'];
        //         $total++;
        //     }

        //     return round($soma / $total, 2);
        // }

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

        public function salasArtistasAtivas($conn) {
            $stmt = $conn->prepare(
                "SELECT s.id_sala, s.nome, u.username, u.icon
                FROM sala s
                OUTER APPLY (
                    SELECT TOP 1 mu.id_usuario
                    FROM MusicaSala mu
                    WHERE mu.id_sala = s.id_sala AND mu.id_musica IS NOT NULL
                ) AS ms
                JOIN usuario u ON ms.id_usuario = u.id_usuario
                WHERE s.tipo_sala = 2
                AND s.data_criacao >= DATEADD(day, -7, dbo.datacorreta());");

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function salasArtistasAtivasLogado($conn, $idUsuario) {
            $stmt = $conn->prepare(
                "SELECT s.id_sala, s.nome, u.username, u.icon, (
                    select case when exists (select id_sala from MusicaSala where id_sala = s.id_sala and id_usuario = :usuario)
                            then 1
                            else 0
                        end)as participante
                FROM sala s
                OUTER APPLY (
                    SELECT TOP 1 mu.id_usuario
                    FROM MusicaSala mu
                    WHERE mu.id_sala = s.id_sala AND mu.id_musica IS NOT NULL
                ) AS ms
                JOIN usuario u ON ms.id_usuario = u.id_usuario
                WHERE s.tipo_sala = 2
                AND s.data_criacao >= DATEADD(day, -7, dbo.datacorreta());");
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->execute();

            $resposta = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resposta as &$row) {
                $row['participante'] = $row['participante'] == 1 ? true : false;
            }

            return $resposta;
        }

        public function criaSalaArtista($conn, $idUsuario, $musicas) {
            $stmt = $conn->prepare(
                "SP_CRIA_SALA_ARTISTA :usuario, :musica0, :musica1, :musica2, :musica3, :musica4, :musica5, :musica6");
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':musica0', $musicas[0]);
            $stmt->bindParam(':musica1', $musicas[1]);
            $stmt->bindParam(':musica2', $musicas[2]);
            $stmt->bindParam(':musica3', $musicas[3]);
            $stmt->bindParam(':musica4', $musicas[4]);
            $stmt->bindParam(':musica5', $musicas[5]);
            $stmt->bindParam(':musica6', $musicas[6]);

            $stmt->execute();

            return $stmt->fetchColumn();
        }

        public function entraSalaArtista($conn, $sala, $idUsuario) {
            $stmt = $conn->prepare("SP_INSERE_USUARIO_SALA_ARTISTA :sala, :usuario");
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':sala', $sala);

            $stmt->execute();

            return $stmt->fetchColumn();
        }

        public function getArtista($conn, $sala, $idUsuario) {
            $stmt = $conn->prepare(
                "SELECT top 1 ms.id_usuario from MusicaSala ms join Sala s on s.id_sala = ms.id_sala 
                where s.id_sala = :sala and s.tipo_sala = 2 and ms.id_musica is not null

                select top 1 id_usuario from MusicaSala where id_usuario = :usuario and id_sala = :idsala");
            $stmt->bindParam(':sala', $sala);
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':idsala', $sala);

            $stmt->execute();

            $idUsuario = $stmt->fetchColumn();

            if ($idUsuario === null) return null;

            $stmt->nextRowset();

            if ($stmt->fetchColumn() === null) return 0;

            return $idUsuario;
        }

        public function getSalaArtistaTotal($conn, $idSala, $idUsuario) {
            $stmt = $conn->prepare('SP_PAINEL_SALA_ARTISTA :sala, :usuario');
            $stmt->bindParam(':sala', $idSala);
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                "sala" => $result["sala"],
                "data_criacao" => $result["data_criacao"],
                "sala_finalizada" => $result["sala_finalizada"] == 1,
                "usuarios" => $result["qtd_usuarios"],
                "avaliacoes" => $result["qtd_avaliacoes"],
                //"participantes" => $this->getParticipantesArtista($conn, $idSala),
                "musicas" => $this->getMusicasArtista($conn, $idSala, (new DateTime())->diff(new DateTime($result["data_criacao"]))->days)
            ];
        }

        private function getParticipantesArtista($conn, $idSala) {
            $stmt = $conn->prepare(
                "SELECT B.username AS nick, B.icon
                FROM MusicaSala A
                INNER JOIN Usuario B ON A.id_usuario = B.id_usuario
                WHERE A.id_sala = :sala and id_musica is null");

            $stmt->bindParam(':sala', $idSala);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $key => $row) {
                $result[$key]['icon'] = str_replace(' ', '', $row['icon']);
            }
            
            return $result;
        }

        private function getMusicasArtista($conn, $idSala, $dias) {
            $stmt = $conn->prepare(
                "SELECT id_musica, nota_calculada, 
                (SELECT COUNT(*) FROM Avaliacao a WHERE a.id_musicasala = ms.id_musicasala) AS avaliacoes 
                from MusicaSala ms where id_sala = :sala and id_musica is not null order by ordem_sala");
            $stmt->bindParam(':sala', $idSala);
            $stmt->execute();

            $musicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            for ($i = 0; $i < 7; $i++) {
                if ($i <= $dias){
                    $musicas[$i]['exibida'] = true;
                } else{
                    $musicas[$i]['exibida'] = false;
                    unset($musicas[$i]['nota_calculada']);
                    unset($musicas[$i]['avaliacoes']);
                }
            }
            return $musicas;
        }

        public function getSalaArtista($conn, $idSala, $idUsuario) {
            $stmt = $conn->prepare(
                "SELECT
                    MS.id_musicasala AS id_musica_sala,
                    MS.id_musica AS musica,
                    CASE WHEN A.nota IS NULL THEN NULL ELSE MS.nota_calculada END AS avaliacao_media,
                    A.nota AS nota_usuario
                FROM Sala AS S
                INNER JOIN MusicaSala AS MS ON S.id_sala = MS.id_sala
                LEFT JOIN Avaliacao AS A ON MS.id_musicasala = A.id_musicasala AND A.id_usuario = :usuario
                WHERE S.id_sala = :sala
                AND dbo.datacorreta() > S.data_criacao + MS.ordem_sala - 1
                ORDER BY MS.ordem_sala;
                
                EXEC SP_INFO_SALA_ARTISTA :idsala");
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':sala', $idSala);
            $stmt->bindParam(':idsala', $idSala);
            $stmt->execute();

            $musicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->nextRowset();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                "sala" => $result["sala"],
                "tempo_restante" => $result["tempo_restante"],
                "sala_finalizada" => $result["sala_finalizada"] == 1,
                "nick_artista" => $result["username"],
                "icon_artista" => $result["icon"],
                "musicas" => $musicas
            ];
        }
    } 
?>