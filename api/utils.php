<?php

require_once '../libs/redbean/rb.php';
include_once "../libs/wideimage/WideImage.php";

function databaseConnect($valida = true, $redirect = '') {
   if ($valida && (!usuarioLogado())) {
      if ($redirect) {
         header('location:./');
      } else if ($redirect <> '') {
         header('location:' . $redirect);
      }
   }
   if (!R::hasDatabase('default')){
      R::setup('mysql:host=localhost;dbname=pi', 'pi', '');
      R::setAutoResolve( TRUE); 
   }
}

function validaUsuario($usuario, $senha) {
   databaseConnect(false);
   return (R::count('usuarios', "email = '$usuario' AND senha = MD5('$senha')") > 0);
}

function efetuaLogin($usuario, $senha) {
   databaseConnect(false);
   $user = R::findOne('usuarios', "email = '$usuario' AND senha = MD5('$senha')");
   if ($user != NULL) {
      iniciarSessao();
      $_SESSION['usuario'] = $user;
   }
}

function efetuaLogout() {
   iniciarSessao();
   unset($_SESSION['usuario']);
   session_destroy();
}

function usuarioLogado() {
   iniciarSessao();
   return (isset($_SESSION['usuario']) && ($_SESSION['usuario'] != NULL));
}

function iniciarSessao() {
   if (!isset($_SESSION)) {
      session_start();
   }
}

function printImageUser($id, $w, $h) {
   header("Content-type: image/jpeg");
   databaseConnect(true, true);
   if (!file_exists('./../cache/media'))
      mkdir('./../cache/media', 0777, true);
   if (!file_exists('./../cache/media/' . $id . '.jpg')) {
      $PDO = R::getDatabaseAdapter()->getDatabase()->getPDO();
      $query = $PDO->prepare('SELECT * FROM files WHERE id = :id');
      $query->bindParam(':id', $id);
      $query->execute();
      if ($row = $query->fetch()) {
         file_put_contents('./../cache/media/' . $id . '.jpg', $row['file']);
         resizeImage($row['file'], $w, $h);
      }
   } else {
      resizeImage('./../cache/media/' . $id . '.jpg', $w, $h);
   }

   //$file = R::load('files', $id);
   //resizeImage($file->file, $w, $h);
}

function getEstilos() {
   databaseConnect(true, true);
   $estilos = R::findAll('estilos');   
   $estilos = R::exportAll($estilos, FALSE, ['']);
   return $estilos;
}

function getInfluencias() {
   databaseConnect(true, true);
   $influencias = R::findAll('influencias');   
   $influencias = R::exportAll($influencias, FALSE, ['']);
   return $influencias;
}

function getInstrumentos() {
   databaseConnect(true, true);
   $instrumentos = R::findAll('instrumentos');   
   $instrumentos = R::exportAll($instrumentos, FALSE, ['']);
   foreach ($instrumentos as &$instrumento) {
      $instrumento['nivel'] = 0;      
   }
   return $instrumentos;
}

function jsonDataUser($id) {
   header('Content-Type: application/json');
   databaseConnect(true, true);
   $user = R::load('usuarios', $id);
   $data = $user->export();
   fixUserData($data);
   $data['usuarios_instrumentos'] = getInstrumentosUsuario($data['id']);
   $data['usuarios_estilos'] = getEstilosUsuario($data['id']);
   $data['estilos'] = pegaIds($data['usuarios_estilos'], 'estilos_id');
   $data['usuarios_influencias'] = getInfluenciasUsuario($data['id']);
   $data['influencias'] = pegaIds($data['usuarios_influencias'], 'influencias_id');
   //$data['posts'] = getPostsUsuario($data['id']);
   $data = json_encode($data, JSON_PRETTY_PRINT);
   echo $data;
}

function pegaIds($array,$index){
   $nArray = array();
   foreach ($array as $item)
      $nArray[] = $item[$index];
   return $nArray;
}

function getInstrumentosUsuario($id) {
   databaseConnect(true, true);
   $instrumentos = R::findAll('usuarios_instrumentos', "usuarios_id = $id");
   $instrumentos = R::exportAll($instrumentos, TRUE, ['instrumentos']);
   return $instrumentos;
}

function getEstilosUsuario($id) {
   databaseConnect(true, true);
   $estilos = R::findAll('usuarios_estilos', "usuarios_id = $id");
   $estilos = R::exportAll($estilos, TRUE, ['estilos']);
   return $estilos;
}

function getInfluenciasUsuario($id) {
   databaseConnect(true, true);
   $influencias = R::findAll('usuarios_influencias', "usuarios_id = $id");
   $influencias = R::exportAll($influencias, TRUE, ['influencias']);
   return $influencias;
}

function fixPostData(&$post) {
   iniciarSessao();
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
   databaseConnect(true, true);
   $posts = R::findAll('posts', "usuarios_id = $id ORDER BY data_hora DESC");
   $posts = R::exportAll($posts, TRUE, ['usuarios']);
   foreach ($posts as &$post) {
      fixPostData($post);
   }
   return $posts;
}

function getPost($id) {
   databaseConnect(true, true);
   $post = R::load('posts', $id);
   $post = $post->export(false, true, false, ['usuarios']);
   fixPostData($post);
   return $post;
}

function getLikesPost($id) {
   databaseConnect(true, true);
   iniciarSessao();
   $likes = R::findAll('likes', "posts_id = $id");
   $likes = R::exportAll($likes, TRUE, ['usuarios']);
   foreach ($likes as &$like) {
      fixUserData($like['usuarios']);
   }
   return $likes;
}

function isLiked($id) {
   databaseConnect(true, true);
   iniciarSessao();
   $likes = R::findAll('likes', "posts_id = $id and usuarios_id = " . $_SESSION['usuario']->id);
   return (count($likes) > 0);
}

function getComentariosPost($id) {
   databaseConnect(true, true);
   iniciarSessao();
   $comentarios = R::findAll('comentarios', "posts_id = $id ORDER BY data_hora");
   $comentarios = R::exportAll($comentarios, TRUE, ['usuarios']);
   foreach ($comentarios as &$comentario) {
      fixUserData($comentario['usuarios']);
      $comentario['canEdit'] = ($_SESSION['usuario']->id == $comentario['usuarios']['id']);
   }
   return $comentarios;
}

function getComentario($id) {
   databaseConnect(true, true);
   iniciarSessao();
   $comentario = R::load('comentarios', $id);
   $comentario = $comentario->export(FALSE, TRUE, FALSE, ['usuarios']);
   fixUserData($comentario['usuarios']);
   $comentario['canEdit'] = ($_SESSION['usuario']->id == $comentario['usuarios']['id']);
   return $comentario;
}

function fixUserData(&$usuario) {
   unset($usuario['senha']);
}

function comentarPost($id, $texto) {
   databaseConnect(true, true);
   iniciarSessao();
   $comentario = R::dispense('comentarios');
   $comentario->posts_id = $id;
   $comentario->usuarios_id = $_SESSION['usuario']->id;
   date_default_timezone_set('America/Sao_Paulo');
   $comentario->data_hora = date('Y-m-d H:i:s', time());
   $comentario->texto = $texto;
   $id = R::store($comentario);
   header('Content-Type: application/json');
   echo json_encode(getComentario($id), JSON_PRETTY_PRINT);
}

function editarComentario($id, $texto) {
   databaseConnect(true, true);
   $comentario = R::load('comentarios', $id);
   date_default_timezone_set('America/Sao_Paulo');
   $comentario->data_hora = date('Y-m-d H:i:s', time());
   $comentario->texto = $texto;
   R::store($comentario);
}

function removerComentario($id) {
   databaseConnect(true, true);
   R::trash('comentarios', $id);
}

function postar($texto) {
   databaseConnect(true, true);
   iniciarSessao();
   $post = R::dispense('posts');
   $post->usuarios_id = $_SESSION['usuario']->id;
   date_default_timezone_set('America/Sao_Paulo');
   $post->data_hora = date('Y-m-d H:i:s', time());
   $post->texto = $texto;
   $id = R::store($post);
   header('Content-Type: application/json');
   echo json_encode(getPost($id), JSON_PRETTY_PRINT);
}

function editarPost($id, $texto) {
   databaseConnect(true, true);
   $post = R::load('posts', $id);
   date_default_timezone_set('America/Sao_Paulo');
   $post->data_hora = date('Y-m-d H:i:s', time());
   $post->texto = $texto;
   R::store($post);
}

function removerPost($id) {
   databaseConnect(true, true);
   R::trash('posts', $id);
}

function resizeImage($binary, $w, $h) {
   $image = WideImage::load($binary);
   $resized = $image->resizeDown($w, $h);
   $resized->output('jpg', 72);
}

function unLike($id) {
   databaseConnect(true, true);
   iniciarSessao();
   $likes = R::findAll('likes', "posts_id = $id and usuarios_id = " . $_SESSION['usuario']->id);
   if (count($likes) > 0) {
      R::trashAll($likes);
   }
}

function like($id) {
   databaseConnect(true, true);
   iniciarSessao();
   $like = R::dispense('likes');
   $like->usuarios_id = $_SESSION['usuario']->id;
   $like->posts_id = $id;
   R::store($like);
}

function updateUser($dados) {
   databaseConnect(true, true);
   iniciarSessao();
   
   $usuario = R::load('usuarios', $_SESSION['usuario']->id);
   $usuario->nome = $dados->nome;
   if (is_object($dados->localizacao)) {
      $usuario->localizacao = $dados->localizacao->formatted_address;
   } else {
      $usuario->localizacao = $dados->localizacao;
   }   
   R::store($usuario);
  
   $PDO = R::getDatabaseAdapter()->getDatabase()->getPDO();
   $PDO->exec('DELETE FROM usuarios_estilos WHERE usuarios_id = '.$_SESSION['usuario']->id);
   $query = $PDO->prepare('INSERT INTO usuarios_estilos VALUES(null, :usuario, :estilo)');      
   foreach ($dados->estilos as $estilo) {
      $query->bindParam(':usuario', $_SESSION['usuario']->id);
      $query->bindParam(':estilo', $estilo);
      $query->execute();      
   } 

   $PDO->exec('DELETE FROM usuarios_influencias WHERE usuarios_id = '.$_SESSION['usuario']->id);
   $query = $PDO->prepare('INSERT INTO usuarios_influencias VALUES(null, :usuario, :influencia)');         
   foreach ($dados->influencias as $influencia) {
      $query->bindParam(':usuario', $_SESSION['usuario']->id);
      $query->bindParam(':influencia', $influencia);
      $query->execute();      
   }   
}
