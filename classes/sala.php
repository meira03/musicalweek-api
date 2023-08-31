<?php
    class Sala {
        private $idMusicaSala;
    
        public function __construct($idMusicaSala) {
            $this->idMusicaSala = $idMusicaSala;
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
        
        public function verifica($conn) {
            $stmt = $conn->prepare('SELECT id_sala FROM MusicaSala WHERE id_musicasala = :id_musicasala');
            $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
            $stmt->execute();
            
            if ($stmt->fetch(PDO::FETCH_COLUMN) == null){
                return false;
            } else {
                return true;
            }
        }

        public function getFila($conn) {
            $stmt = $conn->prepare(
                'SELECT MS.id_sala AS sala, MS.id_musica AS musica
                FROM MusicaSala MS
                WHERE MS.id_musicasala = :id_musicasala;');
            $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        }
        public function getInfo($conn) {
            $stmt = $conn->prepare(
                'SELECT B.nome AS sala, b.data_inicio + T.ordem_sala AS tempo_restante,T.ordem_sala
                from MusicaSala A
        INNER JOIN Sala B on A.id_sala = B.id_sala
        LEFT JOIN (SELECT top 1 B.id_sala, ordem_sala from MusicaSala A
            INNER JOIN Sala B on A.id_sala = B.id_sala
            where :dataatual < B.data_criacao + ordem_sala
            and A.id_sala = (select id_sala from MusicaSala where id_musicasala = :id_musicasala)
            order by ordem_sala) as T ON A.id_sala = T.id_sala
        where A.id_musicasala = :idmusicasala
            ');
            $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
            $stmt->bindParam(':idmusicasala', $this->idMusicaSala);
            $stmt->bindParam(':dataatual', $dataatual);
            $dataatual = date("Y-m-d H:i:s");
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $info = [
                "sala" => $result["sala"],
                "tempo_restante" => $result["tempo_restante"],
                "sala_finalizada" => $this->statusSala($conn),
                "participantes" => $this->getParticipantes($conn),
                "musicas" => $this->getMusicas($conn)
            ];

            return $info;
        }

        private function statusSala($conn) {
            $stmt = $conn->prepare("SELECT B.data_inicio, B.qtd_usuarios
                       FROM Sala B
                       INNER JOIN MusicaSala MS ON MS.id_sala = B.id_sala
                       WHERE MS.id_musicasala = :id_musicasala");
            $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $data_inicio = $result["data_inicio"];
            $qtd_usuarios = $result["qtd_usuarios"];

            $data_final = date("Y-m-d H:i:s", strtotime($data_inicio . " + $qtd_usuarios days"));

            return $data_final < date("Y-m-d H:i:s");
        }

        private function getParticipantes($conn) {
            $stmt = $conn->prepare(
            "SELECT B.username AS nick, B.icon
            FROM MusicaSala A
            INNER JOIN Usuario B ON A.id_usuario = B.id_usuario
            WHERE A.id_sala = (SELECT id_sala FROM MusicaSala WHERE id_musicasala = :id_musicasala)
        ");

        $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key => $row) {
            $result[$key]['icon'] = str_replace(' ', '', $row['icon']);
        }
         
        return $result ;
        }

        private function getMusicas($conn) {
            $stmt = $conn->prepare(
                "SELECT B.id_musicasala from Sala A
                INNER JOIN MusicaSala B on A.id_sala = B.id_sala
                where B.id_sala = (select id_sala from MusicaSala where id_musicasala = :id_musicasala)
            ");
            $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
            $stmt->execute();

            $idsMusicaSala = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare(
                "SELECT top 1 A.id_musicasala from MusicaSala A
                INNER JOIN Sala B on A.id_sala = B.id_sala
                where :dataatual < B.data_criacao + ordem_sala
                and A.id_sala = (select id_sala from MusicaSala where id_musicasala = :id_musicasala)
                order by ordem_sala");
            $stmt->bindParam(':id_musicasala', $this->idMusicaSala);
            $stmt->bindParam(':dataatual', $dataAtual);
            $dataAtual = date("Y-m-d H:i:s");
            $stmt->execute();

            $musicaAtual = $stmt->fetch(PDO::FETCH_COLUMN);

            $musicas = array();

            foreach ($idsMusicaSala as $row) {
                $musicaId = $row['id_musicasala'];
                $stmt = $conn->prepare(
                    "SELECT A.id_musicasala id_musica_sala, A.id_musica musica, A.ordem_sala ordem,
                    T.media_nota AS avaliacao_media, B.nota nota_usuario
                    FROM MusicaSala A
                    LEFT JOIN Avaliacao B ON A.id_musicasala = B.id_musicasala 
                    AND B.id_usuario = (SELECT id_usuario FROM MusicaSala WHERE id_musicasala = :idmusicasala)
                    LEFT JOIN MusicaSala C ON A.id_musicasala = C.id_musicasala
                    INNER JOIN (
                        SELECT id_musica, AVG(nota) AS media_nota
                        FROM MusicaSala A
                        LEFT JOIN Avaliacao B ON A.id_musicasala = B.id_musicasala
                        GROUP BY id_musica
                    ) T ON A.id_musica = T.id_musica
                    WHERE A.id_musicasala = :musicaid");
                $stmt->bindParam(':idmusicasala', $this->idMusicaSala);
                $stmt->bindParam(':musicaid', $musicaId);

                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                array_push($musicas, [
                    "id_musica_sala" => $result["id_musica_sala"],
                    "musica" => $result["musica"],
                    "ordem" => $result["ordem"],
                    "avaliacao_media" =>  $result["avaliacao_media"],
                    "nota_usuario" => $result["nota_usuario"],
                    "avaliacoes" => $this->getAvaliacoes($conn, $musicaId)
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

        private function getAvaliacoes($conn, $musicaId) {
            $stmt = $conn->prepare(
                "SELECT U.username AS nick, A.nota
                FROM MusicaSala MS
                LEFT JOIN Avaliacao A ON MS.id_musicasala = A.id_musicasala
                LEFT JOIN Usuario U ON A.id_usuario = U.id_usuario
                WHERE MS.id_musica = (select id_musica from MusicaSala where id_musicasala = :musicaid)
                and MS.id_sala = (select id_sala from MusicaSala where id_musicasala = :idmusica)");
            $stmt->bindParam(':musicaid', $musicaId);
            $stmt->bindParam(':idmusica', $musicaId);

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
                "Select id_musicasala from MusicaSala where id_usuario = :usuario and id_sala = :sala");
            $stmt->bindParam(':usuario', $idUsuario);
            $stmt->bindParam(':sala', $sala);

            $stmt->execute();

            $resposta = $stmt->fetchColumn();

            if ($resposta == null) {
                return false;
            } else {
                $this->idMusicaSala = $resposta;
                return true;
            }
        }

        public function getIdMusicaSala() {
            return $this->idMusicaSala;
        }
    } 
?>