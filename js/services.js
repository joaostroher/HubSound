angular.module('hubsound')
   .service('mapService', function ($http) {
      this.getMapMarkers = function (filtros) {
         var data = {};
         data.acao = 'getMapMarkers';
         data.filtros = filtros;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.getMapUsers = function (latitude, longitude, filtros) {
         var data = {};
         data.acao = 'getMapUsers';
         data.latitude = latitude;
         data.longitude = longitude;
         data.filtros = filtros;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
   })
   .service('musicBrainz', function ($http) {
      this.getArtists = function (val) {
         var data = {};
         data.query = val;
         data.limit = 100;
         data.fmt = 'json';
         var ret = $http({
            method: 'GET',
            url: 'http://musicbrainz.org/ws/2/artist',
            params: data
         });
         return ret;
      };
   })
   .service('dataService', function ($http) {
      this.getEstilos = function () {
         var data = {};
         data.acao = 'getEstilos';
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.getInfluencias = function () {
         var data = {};
         data.acao = 'getInfluencias';
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.getInstrumentos = function () {
         var data = {};
         data.acao = 'getInstrumentos';
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
   })
   .service('userService', function ($http, $rootScope, $location) {
      this.userIsOn = function () {
         var data = {};
         data.acao = 'usuarioLogado';
         data.logado = false;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.logout = function () {
         var data = {};
         data.acao = 'efetuaLogout';
         $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         $location.path('/login.html');
      };
      this.userData = function () {
         var data = {};
         data.acao = 'userData';
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.userDataId = function (id) {
         var data = {};
         data.acao = 'userData';
         data.id = id;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.updateUser = function (dados) {
         var data = {};
         data.acao = 'updateUser';
         data.dados = dados;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.changeCurrentUser = function (id) {
         var data = {};
         data.acao = 'changeCurrentUser';
         data.id = id;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.getUserOptions = function () {
         var data = {};
         data.acao = 'getUserOptions';         
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
      this.addImage = function (base64) {
         var data = {};
         data.acao = 'addImage';
         data.imagem = base64;
         var ret = $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
         return ret;
      };
   })

   .service('postService', function ($http) {
      this.postar = function (post) {
         var data = {};
         data.acao = 'postar';
         data.post = post;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.editarPost = function (id, texto) {
         var data = {};
         data.acao = 'editarPost';
         data.id = id;
         data.texto = texto;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.removerPost = function (id) {
         var data = {};
         data.acao = 'removerPost';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.comentarPost = function (id, comentario) {
         var data = {};
         data.acao = 'comentar';
         data.id = id;
         data.comentario = comentario;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.editarComentario = function (id, comentario) {
         var data = {};
         data.acao = 'editarComentario';
         data.id = id;
         data.comentario = comentario;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.removerComentario = function (id) {
         var data = {};
         data.acao = 'removerComentario';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.getPostData = function (id) {
         var data = {};
         data.acao = 'postData';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.like = function (id) {
         var data = {};
         data.acao = 'like';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.unLike = function (id) {
         var data = {};
         data.acao = 'unLike';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.userPostData = function () {
         var data = {};
         data.acao = 'userPostData';
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.userPostDataId = function (id) {
         var data = {};
         data.acao = 'userPostData';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
      this.getLikesPost = function (id) {
         var data = {};
         data.acao = 'getLikesPost';
         data.id = id;
         return $http({
            method: 'POST',
            url: 'api/acao.php',
            params: data
         });
      };
   });