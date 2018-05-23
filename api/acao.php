<?php

require_once './utilsPDO.php';

if (isset($_REQUEST['acao'])) {
   if ($_REQUEST['acao'] == 'efetuaLogin') {
      $array = array();
      $validou = validaUsuario($_REQUEST['usuario'], $_REQUEST['senha']);
      $array['validou'] = $validou;
      if ($validou) {
         efetuaLogin($_REQUEST['usuario'], $_REQUEST['senha']);
      }
      echo json_encode($array);
   } else if ($_REQUEST['acao'] == 'usuarioLogado') {
      $array = array();
      $logado = usuarioLogado();
      $array['logado'] = $logado;
      echo json_encode($array);
   } else if ($_REQUEST['acao'] == 'efetuaLogout') {
      efetuaLogout();
   } else if ($_REQUEST['acao'] == 'getImageProfile') {
      if (isset($_REQUEST['id'])) {
         $id = $_REQUEST['id'];
      } else {
         iniciarSessao();
         $id = $_SESSION['usuario']->imagem;
      }
      if (isset($_REQUEST['w']) && isset($_REQUEST['h'])) {
         $w = $_REQUEST['w'];
         $h = $_REQUEST['h'];
      } else {
         $w = 48;
         $h = 48;
      }
      printImageUser($id, $w, $h);
   } else if ($_REQUEST['acao'] == 'userData') {
      if (isset($_REQUEST['id'])) {
         $id = $_REQUEST['id'];
      } else {
         iniciarSessao();
         $id = $_SESSION['usuario']->id;
      }
      jsonDataUser($id);
   } else if ($_REQUEST['acao'] == 'changeCurrentUser') {
      if (isset($_REQUEST['id'])) {
         changeCurrentUser($_REQUEST['id']);
      }
   } else if ($_REQUEST['acao'] == 'userPostData') {
      if (isset($_REQUEST['id'])) {
         $id = $_REQUEST['id'];
      } else {
         iniciarSessao();
         $id = $_SESSION['usuario']->id;
      }
      header('Content-Type: application/json');
      echo json_encode(getPostsUsuario($id), JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'postData') {
      if (isset($_REQUEST['id'])) {
         $id = $_REQUEST['id'];
         $post = getPost($id);
         $data = json_encode($post, JSON_PRETTY_PRINT);
         header('Content-Type: application/json');
         echo $data;
      }
   } else if ($_REQUEST['acao'] == 'comentar') {
      if (isset($_REQUEST['id']) && isset($_REQUEST['comentario'])) {
         comentarPost($_REQUEST['id'], $_REQUEST['comentario']);
      }
   } else if ($_REQUEST['acao'] == 'editarComentario') {
      if (isset($_REQUEST['id']) && isset($_REQUEST['comentario'])) {
         editarComentario($_REQUEST['id'], $_REQUEST['comentario']);
      }
   } else if ($_REQUEST['acao'] == 'removerComentario') {
      if (isset($_REQUEST['id'])) {
         removerComentario($_REQUEST['id']);
      }
   } else if ($_REQUEST['acao'] == 'postar') {
      if (isset($_REQUEST['post'])) {
         postar(json_decode($_REQUEST['post']));
      }
   } else if ($_REQUEST['acao'] == 'editarPost') {
      if (isset($_REQUEST['id']) && isset($_REQUEST['texto'])) {
         editarPost($_REQUEST['id'], $_REQUEST['texto']);
      }
   } else if ($_REQUEST['acao'] == 'removerPost') {
      if (isset($_REQUEST['id'])) {
         removerPost($_REQUEST['id']);
      }
   } else if ($_REQUEST['acao'] == 'like') {
      if (isset($_REQUEST['id'])) {
         like($_REQUEST['id']);
      }
   } else if ($_REQUEST['acao'] == 'unLike') {
      if (isset($_REQUEST['id'])) {
         unLike($_REQUEST['id']);
      }
   } else if ($_REQUEST['acao'] == 'updateUser') {
      if (isset($_REQUEST['dados'])) {
         $json = json_decode($_REQUEST['dados']);
         updateUser($json);
      }
   } else if ($_REQUEST['acao'] == 'getEstilos') {
      $estilos = getAll('estilos order by descricao');
      header('Content-Type: application/json');
      echo json_encode($estilos, JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'getInfluencias') {
      $influencias = getAll('influencias order by descricao');
      header('Content-Type: application/json');
      echo json_encode($influencias, JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'getInstrumentos') {
      $instrumentos = getAll('instrumentos order by descricao');
      header('Content-Type: application/json');
      echo json_encode($instrumentos, JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'getLikesPost') {
      if (isset($_REQUEST['id'])) {
         $likes = getLikesPost($_REQUEST['id']);
         header('Content-Type: application/json');
         echo json_encode($likes, JSON_PRETTY_PRINT);
      }
   } else if ($_REQUEST['acao'] == 'getMapMarkers') {
      $markers = getMapMarkers(json_decode($_REQUEST['filtros']));
      header('Content-Type: application/json');
      echo json_encode($markers, JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'getMapUsers') {
      if (isset($_REQUEST['latitude']) && isset($_REQUEST['longitude'])) {
         $users = getMapUsers($_REQUEST['latitude'], $_REQUEST['longitude'], json_decode($_REQUEST['filtros']));
         header('Content-Type: application/json');
         echo json_encode($users, JSON_PRETTY_PRINT);
      }
   } else if ($_REQUEST['acao'] == 'addImage') {
      $data = addImage();
      header('Content-Type: application/json');
      echo json_encode($data, JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'reportMapUsers') {
      reportMapUsers(json_decode($_REQUEST['filtros']));
   } else if ($_REQUEST['acao'] == 'csvMapUsers') {
      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename=userslist.csv');
      header('Pragma: no-cache');
      header("Expires: 0");
      echo csvMapUsers(json_decode($_REQUEST['filtros']));
   } else if ($_REQUEST['acao'] == 'getUserOptions') {
      $options = getUserOptions();
      header('Content-Type: application/json');
      echo json_encode($options, JSON_PRETTY_PRINT);
   } else if ($_REQUEST['acao'] == 'createUser') {
      if (isset($_REQUEST['dados'])) {
         $result = createUser(json_decode($_REQUEST['dados']));
         header('Content-Type: application/json');
         echo json_encode($result, JSON_PRETTY_PRINT);
      }
   }
}
   