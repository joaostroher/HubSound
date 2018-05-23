<?php

include_once "../libs/wideimage/WideImage.php";
require_once ('../libs/fpdf/fpdf.php');

$PDO = null;

//CONEXAO E SESSION

function databaseConnect($valida = true, $redirect = '') {
   global $PDO;
   if ($valida && (!usuarioLogado())) {
      if ($redirect) {
         header('location:./');
      } else if ($redirect <> '') {
         header('location:' . $redirect);
      }
      return false;
   } else if ($PDO == null) {
      $PDO = new PDO('mysql:host=127.0.0.1;dbname=pi;charset=utf8', 'pi', '');
   }
   return $PDO;
}

function validaUsuario($usuario, $senha) {
   $PDO = databaseConnect(false);
   if ($PDO) {
      $query = $PDO->prepare("select count(*) as count from usuarios
                              where email = :email AND senha = MD5(:senha)");
      $query->bindParam(':email', $usuario);
      $query->bindParam(':senha', $senha);
      $query->execute();
      $result = $query->fetch();
      return ($result['count'] > 0);
   }
}

function efetuaLogin($usuario, $senha) {
   $PDO = databaseConnect(false);
   if ($PDO) {
      $query = $PDO->prepare("select * from usuarios
                              where email = :email AND senha = MD5(:senha)");
      $query->bindParam(':email', $usuario);
      $query->bindParam(':senha', $senha);
      $query->execute();
      if ($result = $query->fetch(PDO::FETCH_OBJ)) {
         iniciarSessao();
         $_SESSION['usuario'] = $result;
         $_SESSION['logado'] = $result;
      }
   }
}

function changeCurrentUser($id) {
   $PDO = databaseConnect(false);
   if ($PDO) {
      $query = $PDO->prepare("select * from usuarios
                              where id = :id");
      $query->bindParam(':id', $id);
      $query->execute();
      if ($result = $query->fetch(PDO::FETCH_OBJ)) {
         iniciarSessao();
         $_SESSION['usuario'] = $result;
      }
   }
}

function efetuaLogout() {
   iniciarSessao();
   unset($_SESSION['logado']);
   unset($_SESSION['usuario']);
   session_destroy();
}

function usuarioLogado() {
   iniciarSessao();
   return (isset($_SESSION['logado']) && ($_SESSION['logado'] != NULL));
}

function iniciarSessao() {
   if (!isset($_SESSION)) {
      session_start();
   }
}

function getFromId($table, $id) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("SELECT * FROM $table where id = :id");
      $query->bindParam(':id', $id);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_ASSOC);
      return $result;
   }
}

function getFrom($table, $field, $filter, $SQL = '') {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("SELECT * FROM $table where $field = :filter $SQL");
      $query->bindParam(':filter', $filter);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      return $result;
   }
}

function getAll($table) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("SELECT * FROM $table");
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      return $result;
   }
}

function deleteFromId($table, $id) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("DELETE FROM $table where id = :id");
      $query->bindParam(':id', $id);
      $query->execute();
   }
}

function insertData($table, $data, $valida = true) {
   $PDO = databaseConnect($valida, true);

   $bind = ':' . implode(',:', array_keys($data));
   $sql = 'insert into ' . $table . '(' . implode(',', array_keys($data)) . ') ' .
         'values (' . $bind . ')';
   $stmt = $PDO->prepare($sql);
   $stmt->execute(array_combine(explode(',', $bind), array_values($data)));

   if ($stmt->rowCount() > 0) {
      return $PDO->lastInsertId();
   }
}

function updateData($table, $data) {
   $id = 0;
   $sql = "UPDATE $table SET";
   $values = array();
   foreach ($data as $name => $value) {
      if ($name != 'id') {
         $sql .= ' ' . $name . ' = :' . $name . ','; // the :$name part is the placeholder, e.g. :zip         
      } else {
         $id = $value;
      }
      $values[':' . $name] = $value; // save the placeholder
   }
   $sql = substr($sql, 0, -1) . " where id = :id";

   $PDO = databaseConnect(true, true);
   $stmt = $PDO->prepare($sql);
   $stmt->execute($values);

   if ($stmt->rowCount() > 0) {
      return true;
   }
}

//OUTRAS FUNCOES

function printImageUser($id, $w, $h) {
   header("Content-type: image/jpeg");
   if (!file_exists('./../cache/media'))
      mkdir('./../cache/media', 0777, true);
   if (file_exists('./../cache/media/' . $id . '.jpg')) {
      resizeImage('./../cache/media/' . $id . '.jpg', $w, $h);
   } else {
      resizeImage('./../res/img/avatar.png', $w, $h);
   }
}

function resizeImage($binary, $w, $h) {
   $image = WideImage::load($binary);
   $resized = $image->resize($w, $h);
   $resized->output('jpg', 100);
}

function pegaIds($array, $index) {
   $nArray = array();
   foreach ($array as $item)
      $nArray[] = $item[$index];
   return $nArray;
}

//PERFIL DO USUARIO

function jsonDataUser($id) {
   header('Content-Type: application/json');
   $user = getFromId('usuarios', $id);
   fixUserData($user);
   $user['usuarios_instrumentos'] = getInstrumentosUsuario($user['id']);
   $user['instrumentos'] = pegaIds($user['usuarios_instrumentos'], 'id');
   $user['usuarios_estilos'] = getEstilosUsuario($user['id']);
   $user['estilos'] = pegaIds($user['usuarios_estilos'], 'estilos_id');
   $user['usuarios_influencias'] = getInfluenciasUsuario($user['id']);
   $user['influencias'] = pegaIds($user['usuarios_influencias'], 'influencias_id');
   //$data['posts'] = getPostsUsuario($data['id']);
   $data = json_encode($user, JSON_PRETTY_PRINT);
   echo $data;
}

function getInstrumentosUsuario($id) {
   $array = getFrom('usuarios_instrumentos', 'usuarios_id', $id);
   $array2 = array();
   foreach ($array as &$data) {
      $instrumento = getFromId('instrumentos', $data['instrumentos_id']);
      $instrumento['nivel'] = $data['nivel'];
      $array2[] = $instrumento;
   }
   return $array2;
}

function getEstilosUsuario($id) {
   $array = getFrom('usuarios_estilos', 'usuarios_id', $id);
   foreach ($array as &$data) {
      $data['estilos'] = getFromId('estilos', $data['estilos_id']);
   }
   return $array;
}

function getInfluenciasUsuario($id) {
   $array = getFrom('usuarios_influencias', 'usuarios_id', $id);
   foreach ($array as &$data) {
      $data['influencias'] = getFromId('influencias', $data['influencias_id']);
   }
   return $array;
}

function fixUserData(&$usuario) {
   unset($usuario['senha']);
}

//POSTAGENS

function fixPostData(&$post) {
   iniciarSessao();
   $post['usuarios'] = getFromId('usuarios', $post['usuarios_id']);
   fixUserData($post['usuarios']);
   $post['canEdit'] = ($_SESSION['usuario']->id == $post['usuarios']['id']);
   $post['comentarios'] = getComentariosPost($post['id']);
   $post['novoComentario']['edit'] = true;
   $post['novoComentario']['texto'] = '';
   $post['limitComentarios'] = true;
   $post['like'] = isLiked($post['id']);
   $post['likes'] = count(getLikesPost($post['id']));
}

function getPostsUsuario($id) {
   $array = getFrom('posts', 'usuarios_id', $id);
   foreach ($array as &$data) {
      fixPostData($data);
   }
   return $array;
}

function getPost($id) {
   databaseConnect(true, true);
   $post = getFromId('posts', $id);
   fixPostData($post);
   return $post;
}

function getLikesPost($id) {
   $array = getFrom('likes', 'posts_id', $id);
   foreach ($array as &$data) {
      $data['usuarios'] = getFromId('usuarios', $data['usuarios_id']);
      fixUserData($data['usuarios']);
   }
   return $array;
}

function isLiked($id) {
   iniciarSessao();
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("SELECT count(*) FROM likes where posts_id = :id and usuarios_id = :usuario");
      $query->bindParam(':id', $id);
      $query->bindParam(':usuario', $_SESSION['usuario']->id);
      $query->execute();
      $result = $query->fetch();
      return ($result[0] > 0);
   }
}

function getComentariosPost($id) {
   iniciarSessao();
   $array = getFrom('comentarios', 'posts_id', $id, 'ORDER BY data_hora');
   foreach ($array as &$data) {
      $data['usuarios'] = getFromId('usuarios', $data['usuarios_id']);
      fixUserData($data['usuarios']);
      $data['canEdit'] = ($_SESSION['usuario']->id == $data['usuarios']['id']);
   }
   return $array;
}

function getComentario($id) {
   iniciarSessao();
   $data = getFromId('comentarios', $id);
   $data['usuarios'] = getFromId('usuarios', $data['usuarios_id']);
   fixUserData($data['usuarios']);
   $data['canEdit'] = ($_SESSION['usuario']->id == $data['usuarios']['id']);
   return $data;
}

//INSERTS E UPDATES

function comentarPost($id, $texto) {
   iniciarSessao();

   $comentario = array();
   $comentario['posts_id'] = $id;
   $comentario['usuarios_id'] = $_SESSION['usuario']->id;
   date_default_timezone_set('America/Sao_Paulo');
   $comentario['data_hora'] = date('Y-m-d H:i:s', time());
   $comentario['texto'] = $texto;

   $id = insertData('comentarios', $comentario);
   if ($id) {
      header('Content-Type: application/json');
      echo json_encode(getComentario($id), JSON_PRETTY_PRINT);
   }
}

function editarComentario($id, $texto) {
   $comentario = array();
   date_default_timezone_set('America/Sao_Paulo');
   $comentario['id'] = $id;
   $comentario['data_hora'] = date('Y-m-d H:i:s', time());
   $comentario['alterado'] = true;
   $comentario['texto'] = $texto;
   updateData('comentarios', $comentario);
}

function removerComentario($id) {
   deleteFromId('comentarios', $id);
}

function postar($data) {
   iniciarSessao();

   $post = array();
   $post['usuarios_id'] = $_SESSION['usuario']->id;
   date_default_timezone_set('America/Sao_Paulo');
   $post['data_hora'] = date('Y-m-d H:i:s', time());
   $post['texto'] = $data->texto;
   $post['url'] = $data->youtube!=''?$data->youtube:null;
   $id = insertData('posts', $post);

   header('Content-Type: application/json');
   echo json_encode(getPost($id), JSON_PRETTY_PRINT);
}

function editarPost($id, $texto) {
   $post = array();
   $post['id'] = $id;
   date_default_timezone_set('America/Sao_Paulo');
   $post['data_hora'] = date('Y-m-d H:i:s', time());
   $post['texto'] = $texto;
   $post['alterado'] = true;

   updateData('posts', $post);
}

function removerPost($id) {
   deleteFromId('posts', $id);
}

function unLike($id) {
   iniciarSessao();
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("DELETE FROM likes where posts_id = :post and usuarios_id = :usuario");
      $query->bindParam(':post', $id);
      $query->bindParam(':usuario', $_SESSION['usuario']->id);
      $query->execute();
   }
}

function like($id) {
   iniciarSessao();
   $like = array();
   $like['usuarios_id'] = $_SESSION['usuario']->id;
   $like['posts_id'] = $id;
   insertData('likes', $like);
}

function updateUser($dados) {
   iniciarSessao();

   $usuario = array();
   if ($dados->newband) {
      $usuario['nome'] = $dados->nome;
      $usuario['imagem'] = $dados->imagem;
      if (is_object($dados->localizacao)) {
         $usuario['localizacao'] = $dados->localizacao->formatted_address;
         $usuario['latitude'] = $dados->localizacao->geometry->location->lat;
         $usuario['longitude'] = $dados->localizacao->geometry->location->lng;
      } else {
         $usuario['localizacao'] = $dados->localizacao;
      }
      $id = insertData('usuarios', $usuario, true);

      $integrante = array();
      $integrante['banda_id'] = $id;
      $integrante['instrumentos_id'] = 1;
      $integrante['usuarios_id'] = $_SESSION['logado']->id;
      $integrante['admin'] = 1;
      insertData('integrantes', $integrante);
      
      header('Content-Type: application/json');
      $arr = array();
      $arr['id'] = $id;
      echo json_encode($arr, JSON_PRETTY_PRINT);
   } else {
      $id = $_SESSION['usuario']->id;
      $usuario['id'] = $_SESSION['usuario']->id;
      $usuario['nome'] = $dados->nome;
      $usuario['imagem'] = $dados->imagem;
      if (is_object($dados->localizacao)) {
         $usuario['localizacao'] = $dados->localizacao->formatted_address;
         $usuario['latitude'] = $dados->localizacao->geometry->location->lat;
         $usuario['longitude'] = $dados->localizacao->geometry->location->lng;
      } else {
         $usuario['localizacao'] = $dados->localizacao;
      }
      updateData('usuarios', $usuario);

      //Atualiza Dados
      $PDO = databaseConnect(false);
      if ($PDO) {
         $query = $PDO->prepare("select * from usuarios where id = :id");
         $query->bindParam(':id', $_SESSION['usuario']->id);
         $query->execute();
         if ($result = $query->fetch(PDO::FETCH_OBJ)) {
            $_SESSION['usuario'] = $result;
         }
      }
   }

   $PDO = databaseConnect(true, true);
   $PDO->exec('DELETE FROM usuarios_estilos WHERE usuarios_id = ' . $id);
   if (isset($dados->estilos) AND is_array($dados->estilos)) {
      $query = $PDO->prepare('INSERT INTO usuarios_estilos VALUES(null, :usuario, :estilo)');
      foreach ($dados->estilos as $estilo) {
         $query->bindParam(':usuario', $id);
         $query->bindParam(':estilo', $estilo);
         $query->execute();
      }
   }

   $PDO->exec('DELETE FROM usuarios_influencias WHERE usuarios_id = ' . $id);
   if (isset($dados->influencias) AND is_array($dados->influencias)) {
      $query = $PDO->prepare('INSERT INTO usuarios_influencias VALUES(null, :usuario, :influencia)');
      foreach ($dados->influencias as $influencia) {
         $query->bindParam(':usuario', $id);
         $query->bindParam(':influencia', $influencia);
         $query->execute();
      }
   }

   $PDO->exec('DELETE FROM usuarios_instrumentos WHERE usuarios_id = ' . $id);
   $query = $PDO->prepare('INSERT INTO usuarios_instrumentos VALUES(null, :usuario, :instrumento, :nivel)');
   if (isset($dados->usuarios_instrumentos) AND is_array($dados->usuarios_instrumentos)) {
      foreach ($dados->usuarios_instrumentos as $instrumento) {
         $query->bindParam(':usuario', $id);
         $query->bindParam(':instrumento', $instrumento->id);
         $query->bindParam(':nivel', $instrumento->nivel);
         $query->execute();
      }
   }
}

function buildMapFilters($filters) {
   $filter = " AND id <> ".($_SESSION['logado']->id)." ";
   if ($filters->tipo == 'b')
         $filter .= " AND (email IS NULL) ";
   else if ($filters->tipo == 'm')
         $filter .= " AND (email IS NOT NULL) ";
   if (is_object($filters)) {
      if ((!empty($filters->estilos)) && is_array($filters->estilos) && (count($filters->estilos) > 0)) {
         $imp = implode(',', $filters->estilos);
         $filter .= "and exists (select * from usuarios_estilos
                                 where usuarios_id = u.id and estilos_id in (" . $imp . "))";
      }
      if ((!empty($filters->influencias)) && is_array($filters->influencias) && (count($filters->influencias) > 0)) {
         $imp = implode(',', $filters->influencias);
         $filter .= "and exists (select * from usuarios_influencias
                                 where usuarios_id = u.id and influencias_id in ($imp))";
      }
      if ((!empty($filters->instrumentos)) && is_array($filters->instrumentos) && (count($filters->instrumentos) > 0)) {
         $imp = '';
         $imp2 = '';
         foreach ($filters->instrumentos as $instrumento) {
            $imp .= ($imp == '' ? '' : ' OR ') . '(instrumentos_id = ' . $instrumento->id . ' and nivel between ' . $instrumento->nivelMin . ' AND ' . $instrumento->nivelMax . ')';
            $imp2 .= ($imp2 == '' ? '' : ' OR ') . '(not exists (select * from usuarios_instrumentos
                                                     where usuarios_id = u.id and instrumentos_id = ' . $instrumento->id . '))';                                                      
         }
         $filter .= "and (((email IS NOT NULL) AND
                           (exists (select * from usuarios_instrumentos
                                    where usuarios_id = u.id and ($imp)))) OR 
                          ((email IS NULL) AND 
                           ($imp2)))";         
      }
   }
   return $filter;
}

function getMapMarkers($filters) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $filters = buildMapFilters($filters);
      $query = $PDO->prepare("select distinct @contador := @contador + 1 AS id, u.latitude, u.longitude, count(*) as quantidade
                              from (SELECT @contador := 0) AS nada, usuarios AS u
                              where latitude <> '' and longitude <> ''
                              $filters
                              group by u.latitude, u.longitude");
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);

      return $result;
   }
}

function getMapUsers($latitude, $longitude, $filters) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $filters = buildMapFilters($filters);
      $query = $PDO->prepare("SELECT u.id, u.nome, u.imagem
                              FROM usuarios as u 
                              WHERE u.latitude = :platitude 
                              AND u.longitude = :plongitude 
                              $filters");
      $query->bindParam(':platitude', $latitude);
      $query->bindParam(':plongitude', $longitude);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);

      return $result;
   }
}

function addImage() {
   $file = base64_decode($_REQUEST['file']);
   $md5 = md5($file);
   $image = WideImage::loadFromString($file);
   $image->saveToFile('./../cache/media/' . $md5 . '.jpg', 72);

   $data = array();
   $data['md5'] = $md5;
   return $data;
}

class ReportMapPDF extends FPDF {

// Page header
   function Header() {
      $this->SetFont('Arial', '', 10);

      $this->Cell(3, 0.5, '', 'TL', 0, 'L');
      $this->SetFont('Arial', 'B', 15);
      $str = iconv('UTF-8', 'windows-1252', 'Listagem');
      $this->Cell(11, 2, $str, 'TB', 0, 'C');
      $this->SetFont('Arial', '', 10);
      $str = iconv('UTF-8', 'windows-1252', 'Página ' . $this->PageNo());
      $this->Cell(3, 0.5, $str, 'TR', 0, 'R');
      $this->Ln(0.5);

      $this->Cell(3, 0.5, '', 'L', 0, 'L');
      $this->Cell(11);
      $this->Cell(3, 0.5, '', 'R', 0, 'R');
      $this->Ln(0.5);

      $this->Cell(3, 0.5, '', 'L', 0, 'L');
      $this->Cell(11);
      $this->Cell(3, 0.5, '', 'R', 0, 'R');
      $this->Ln(0.5);

      $this->Cell(3, 0.5, '', 'BL', 0, 'L');
      $this->Cell(11);
      $this->Cell(3, 0.5, '', 'BR', 0, 'R');
      $this->Ln(1);
   }

}

function reportMapUsers($filtros) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $filters = buildMapFilters($filtros);
      $query = $PDO->prepare("SELECT *
                              FROM usuarios as u 
                              WHERE 1=1 
                              $filters");
      $query->execute();
      $users = $query->fetchAll(PDO::FETCH_ASSOC);
   }

   $pdf = new ReportMapPDF('P', 'cm', 'A4');
   $pdf->SetMargins(2, 2);
   $pdf->AddPage();
   $pdf->SetFont('Arial', '', 12);

   $i = 0;
   foreach ($users as $user) {
      if ($i == 5) {
         $i = 0;
         $pdf->AddPage();
      }
      $mult = (4.5 * $i);

      $pdf->Rect(2, 4.5 + $mult, 17, 4);
      if (($user['imagem'] != '') && (file_exists('./../cache/media/' . $user['imagem'] . '.jpg'))) 
         $pdf->Image('./../cache/media/' . $user['imagem'] . '.jpg', 2.1, 4.6 + $mult, 3.8);
      else
         $pdf->Image('./../res/img/avatar.png', 2.1, 4.6 + $mult, 3.8);
      $pdf->Ln(0.2);
      $pdf->Cell(4);
      $str = iconv('UTF-8', 'windows-1252', $user['nome'] . ($user['email']!=''?' - ' . $user['email']:''));
      $pdf->Cell(4, 0.5, $str);

      $pdf->Ln(0.75);
      $pdf->Cell(4);
      $str = iconv('UTF-8', 'windows-1252', $user['localizacao']);
      $pdf->Cell(4, 0.5, $str);

      $str = '';
      $items = getEstilosUsuario($user['id']);
      foreach ($items as $item) {
         $str .= ($str != '' ? ', ' : '') . $item['estilos']['descricao'];
      }
      $str = 'Estilos: ' . $str;
      $pdf->Ln(0.75);
      $pdf->Cell(4);
      $str = iconv('UTF-8', 'windows-1252', $str);
      $pdf->Cell(4, 0.5, $str);

      $str = '';
      $items = getInfluenciasUsuario($user['id']);
      foreach ($items as $item) {
         $str .= ($str != '' ? ', ' : '') . $item['influencias']['descricao'];
      }
      $str = 'Influências: ' . $str;
      $pdf->Ln(0.75);
      $pdf->Cell(4);
      $str = iconv('UTF-8', 'windows-1252', $str);
      $pdf->Cell(4, 0.5, $str);

      $str = '';
      $items = getInstrumentosUsuario($user['id']);
      foreach ($items as $item) {
         $str .= ($str != '' ? ', ' : '') . $item['descricao'] . ($filtros->tipo=='b'?'(' . $item['nivel'] . ')':'');
      }
      $str = 'Instrumentos: ' . $str;
      $pdf->Ln(0.75);
      $pdf->Cell(4);
      $str = iconv('UTF-8', 'windows-1252', $str);
      $pdf->Cell(4, 0.5, $str);



      $pdf->Ln(1.25);

      $i += 1;
   }



   $pdf->Output();
}

function csvMapUsers($filtros) {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $filters = buildMapFilters($filtros);
      $query = $PDO->prepare("SELECT *
                              FROM usuarios as u 
                              WHERE 1=1 
                              $filters");
      $query->execute();
      $users = $query->fetchAll(PDO::FETCH_ASSOC);
   }
   foreach ($users as $user) {
      echo '"' . $user['nome'] . '",';
      echo '"' . $user['email'] . '",';
      echo '"' . $user['localizacao'] . '"';
      echo PHP_EOL;
   }
}

function getUserOptions() {
   $PDO = databaseConnect(true, true);
   if ($PDO) {
      $query = $PDO->prepare("SELECT * FROM usuarios where id = :id
                              UNION ALL
                              SELECT u.* FROM usuarios u, integrantes i
                              WHERE i.banda_id = u.id
                              AND i.usuarios_id = :id
                              AND i.admin = 1");
      $query->bindParam(':id', $_SESSION['logado']->id);
      $query->execute();
      $bands = $query->fetchAll(PDO::FETCH_ASSOC);
      return $bands;
   }
}

function createUser($dados) {
   $usuario = array();
   $usuario['nome'] = $dados->nome;
   $usuario['email'] = $dados->email;
   $usuario['senha'] = md5($dados->senha);

   $PDO = databaseConnect(false, '');
   if ($PDO) {
      $query = $PDO->prepare("SELECT * FROM usuarios where email = :filter");
      $query->bindParam(':filter', $filter);
      $query->execute();
      $aux = $query->fetchAll(PDO::FETCH_ASSOC);      
   }
   
   $msg = array();
   $msg['mensagem'] = '';
   if ($dados->senha != $dados->rsenha)
      $msg['mensagem'] = 'Senhas não conferem!';
   else if (strlen($dados->senha) < 6) 
      $msg['mensagem'] = 'Senha deve conter pelo menos 6 dígitos.';
   else if (is_array($aux) && (count($aux) > 0))
      $msg['mensagem'] = 'Email já cadastrado!';
   else
      insertData('usuarios', $usuario, false);
   return $msg;
}
