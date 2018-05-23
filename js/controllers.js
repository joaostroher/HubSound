function MainController($scope, userService, $state) {
   $scope.logout = function () {
      userService.logout();
   };
   $scope.login = function () {
      userService.userData().then(function (response) {
         $scope.usuario = response.data;
         userService.getUserOptions().then(function (response) {
            $scope.usuario.options = response.data;
            //$state.reload();            
            $state.go($state.current, {}, {reload: true});
         });
      });
   };
   $scope.goToMe = function () {
      $state.go('index.me');
   };

   $scope.changeCurrentUser = function (id) {
      userService.changeCurrentUser(id).then(function (response) {
         $scope.login();
      });
   };

   $scope.usuario = {};
   $scope.login();

}

function ProfileController($scope, $http, userService, $stateParams, postService, SweetAlert, $timeout, $uibModal, youtubeEmbedUtils) {
   $scope.perfil = {};
   $scope.atualizarUsuario = function () {
      if (($stateParams.uid != null) && ($stateParams.uid != '')) {
         userService.userDataId($stateParams.uid).then(function (response) {
            $scope.perfil = response.data;
            postService.userPostDataId($stateParams.uid).then(function (response) {
               $scope.perfil.posts = response.data;
            });
         });

         $scope.editable = false;
      } else {
         userService.userData().then(function (response) {
            $scope.perfil = response.data;
            postService.userPostData().then(function (response) {
               $scope.perfil.posts = response.data;
            });
         });
         $scope.editable = true;
      }
   };
   $scope.atualizarUsuario();

   $scope.onKeyDownTextArea = function (e, proc, post) {
      if ((e.keyCode === 13) && (!e.ctrlKey)) {
         proc(post);
         e.preventDefault();
      }
   };

   //Comentario
   $scope.novoComentario = function (post) {
      if (post.novoComentario.texto != "") {
         post.novoComentario.edit = false;
         postService.comentarPost(post.id, post.novoComentario.texto).then(function (response) {
            post.novoComentario.texto = "";
            post.novoComentario.edit = true;
            post.comentarios.push(response.data);
         }, function () {
            post.novoComentario.texto = "";
            post.novoComentario.edit = true;
         });
      }
   };
   $scope.iniciaEditarComentario = function (comentario) {
      comentario.editando = true;
   };
   $scope.editarComentario = function (comentario) {
      if (comentario.texto != "") {
         comentario.editando = false;
         postService.editarComentario(comentario.id, comentario.texto).then(function () {
         }, function () {
            $scope.atualizarUsuario();
         });
      }
   };
   $scope.removerComentario = function (post, comentario) {
      SweetAlert.swal({
         title: "Tem certeza?",
         text: "Tem certeza que deseja remover este comentário?",
         showCancelButton: true,
         confirmButtonColor: "#DD6B55",
         confirmButtonText: "Sim",
         cancelButtonText: "Não",
         closeOnConfirm: true,
         closeOnCancel: true},
         function (isConfirm) {
            if (isConfirm) {
               postService.removerComentario(comentario.id).then(function () {
                  post.comentarios.splice(post.comentarios.indexOf(comentario), 1);
               });
            }
         }
      );
   };

   //Post
   $scope.nPost = {};
   $scope.nPost.texto = "";
   $scope.nPost.youtube = "";
   $scope.nPost.edit = true;
   $scope.novoPost = function () {
      if ($scope.nPost.texto != "") {
         $scope.nPost.edit = false;
         postService.postar($scope.nPost).then(function (response) {
            $scope.nPost.texto = "";
            $scope.nPost.youtube = "";
            $scope.nPost.edit = true;
            $scope.perfil.posts.push(response.data);
         }, function () {
            $scope.nPost.texto = "";
            $scope.nPost.youtube = "";
            $scope.nPost.edit = true;
         });
      }
   };
   $scope.iniciaEditarPost = function (post) {
      post.editando = true;
      $timeout(function () {
         $('textarea').trigger("change");
      }, 50);
   };
   $scope.editarPost = function (post) {
      if (post.texto != "") {
         post.editando = false;
         postService.editarPost(post.id, post.texto).then(function () {}, function () {
            $scope.atualizarUsuario();
         });
      }
   };
   $scope.removerPost = function (post) {
      SweetAlert.swal({
         title: "Tem certeza?",
         text: "Tem certeza que deseja remover este comentário?",
         showCancelButton: true,
         confirmButtonColor: "#DD6B55",
         confirmButtonText: "Sim",
         cancelButtonText: "Não",
         closeOnConfirm: true,
         closeOnCancel: true},
         function (isConfirm) {
            if (isConfirm) {
               postService.removerPost(post.id).then(function () {
                  $scope.perfil.posts.splice($scope.perfil.posts.indexOf(post), 1);
               });
            }
         });
   };
   $scope.changeLike = function (post) {
      if (post.like) {
         postService.unLike(post.id).then(function () {
            post.like = false;
            post.likes = post.likes - 1;
         });
      } else {
         postService.like(post.id).then(function () {
            post.like = true;
            post.likes = post.likes + 1;
         });
      }
   };
   $scope.montaTextoLikes = function (like, likes) {
      var text = "";
      if (like) {
         likes = likes - 1;
         if (likes == 0)
            text = "Você curtiu isso"
         else
            text = "Você e mais " + likes + (likes > 1 ? " pessoas" : " pessoa") + " curtiram isso"
      } else if (likes > 0) {
         text = likes + (likes > 1 ? " pessoas curtiram isso" : " pessoa curtiu isso");
      }
      return text;
   };
   $scope.showPostLikes = function (id) {
      var modalInstance = $uibModal.open({
         templateUrl: 'views/likes.html',
         controller: LikesController,
         resolve: {
            getId: function () {
               return id;
            }
         }
      });
   };
   
   $scope.youtubestyle = "";
   $scope.mudoutexto = function() {      
      var strings = $scope.nPost.texto.split("\n");
      for (i in strings){
         var str = strings[i];
         str = str.toUpperCase();
         if (str.indexOf("YOUTUBE.COM") > 0) {
            str = youtubeEmbedUtils.getIdFromURL(strings[i]);
            $scope.youtubestyle = {'color':'#cc181e'};
            $scope.nPost.youtube = str; 
            break;
         }
      }     
   };
   
}

function LoginController($scope, $http, $window, $state) {
   $scope.data = {};
   $scope.data.acao = "efetuaLogin";
   $scope.invalidou = false;

   $scope.loginClick = function () {
      $scope.invalidou = false;
      $http({
         method: 'POST',
         url: 'api/acao.php',
         params: $scope.data
      }).then(
         function (response) {
            if (response.data.validou) {
               $scope.login();
               $state.go('index.me');
            } else {
               $scope.invalidou = true;
            }
         }
      );
   };
}

function SingUpController($scope, $http, $window, $state, $timeout) {
   $scope.usuario = {};
   $scope.mensagem = '';
   $scope.secesso = '';

   $scope.registrar = function () {
      var data = {}
      data.acao = "createUser";
      data.dados = $scope.usuario;
      $http({
         method: 'POST',
         url: 'api/acao.php',
         params: data
      }).then(
         function (response) {
            if (response.data.mensagem != '') {
               $scope.mensagem = response.data.mensagem;
            } else {               
               $scope.sucesso = true;
               $timeout(function(){
                  $state.go('login');
               },1000);
            }
         }
      );
   };
}

function EditProfileController($scope, $http, userService, $state, dataService, uiGmapGoogleMapApi, $uibModal) {
   $scope.createband = $state.is('index.createband');
   uiGmapGoogleMapApi.then(function (map) {
      $scope.geocoder = new map.Geocoder();
   });

   $scope.autocompleteOptionsMap = {
      types: ['(cities)']
   };
   $scope.instrumentosSliderOptions = {
      floor: 1,
      ceil: 10,
      step: 1,
      showTicks: true,
      showTicksValues: true,
      getPointerColor: function (value) {
         return '#18a689';
      }
   };
   $scope.perfil = undefined;
   $scope.estilos = undefined;
   $scope.influencias = undefined;
   $scope.instrumentos = undefined;

   $scope.atualizarUsuario = function () {
      userService.userData().then(function (response) {
         $scope.perfil = response.data;
      });
   };

   $scope.atualizarDados = function () {
      dataService.getEstilos().then(function (response) {
         $scope.estilos = response.data;
      });
      dataService.getInfluencias().then(function (response) {
         $scope.influencias = response.data;
      });
      dataService.getInstrumentos().then(function (response) {
         $scope.instrumentos = response.data;
      });
   };

   $scope.updateUser = function () {
      if ($scope.createband)
         $scope.perfil.newband = true;      
      userService.updateUser(JSON.stringify($scope.perfil)).then(function (response) {
         if ($scope.createband) {
            $state.go('index.me');
            $scope.$parent.changeCurrentUser(response.data.id);            
         } else {
            $state.go('index.me');
            $scope.$parent.login();
         }
      }, function () {
         $scope.atualizarUsuario();
      });
   };

   $scope.changeImage = function () {
      var modalInstance = $uibModal.open({
         templateUrl: 'views/changeimage.html',
         controller: ChangeImageController,
         size: 'lg'
      });
      modalInstance.result.then(function (imagem) {
         if (imagem != null) {
            $scope.perfil.imagem = imagem;
         }
      });
   };

   $scope.atualizarDados();
   if (!$scope.createband)
      $scope.atualizarUsuario();
   else {
      $scope.perfil = {};
      $scope.perfil.email = null;
      $scope.perfil.imagem = '';
   }
   $scope.$watch(
      function (scope) {
         if (scope.perfil != undefined)
            return scope.perfil.instrumentos;
      },
      function (newValue, oldValue) {
         if ((newValue != undefined) && ($scope.instrumentos != undefined) && ($scope.perfil != undefined)) {
            var arr = [];
            for (i in $scope.instrumentos) {
               if (newValue.indexOf($scope.instrumentos[i].id) >= 0) {
                  var instrumento = {};
                  instrumento.id = $scope.instrumentos[i].id;
                  instrumento.descricao = $scope.instrumentos[i].descricao;
                  instrumento.nivel = 1;
                  for (j in $scope.perfil.usuarios_instrumentos) {
                     if ($scope.instrumentos[i].id == $scope.perfil.usuarios_instrumentos[j].id) {
                        instrumento.nivel = $scope.perfil.usuarios_instrumentos[j].nivel;
                     }
                  }
                  arr.push(instrumento);
               }
            }
            $scope.perfil.usuarios_instrumentos = arr;
         }
      });
}

function LikesController($scope, postService, $uibModalInstance, getId, $state) {
   $scope.likes = {};
   postService.getLikesPost(getId).then(function (response) {
      $scope.likes = response.data;
   });
   $scope.enterPerfil = function (id) {
      $state.go("index.profile", {uid: id});
      $uibModalInstance.close();
   };
   $scope.closeModal = function () {
      $uibModalInstance.close();
   };
}

function ChangeImageController($scope, $uibModalInstance, $state, Upload) {
   $scope.myImage = '';
   $scope.myCroppedImage = '';
   $scope.closeModal = function () {
      $uibModalInstance.close(null);
   };
   $scope.atualizarImagem = function () {
      var image = $scope.myCroppedImage;
      image = image.substring(image.indexOf('base64,') + 7, image.length);
      Upload.upload({
         url: 'api/acao.php?acao=addImage',
         method: 'POST',
         file: image
      }).then(function (response) {
         $uibModalInstance.close(response.data.md5);
      });
   };
}

function YouTubeController($scope, $uibModalInstance, $state) {
   $scope.closeModal = function () {
      $uibModalInstance.close(null);
   };
   $scope.atualizarImagem = function () {

   };
}

function MapController($scope, $state, $stateParams, mapService, uiGmapGoogleMapApi, dataService, $window) {
   $scope.filtros = {};
   $scope.filtros.tipo = $stateParams.tipo;
   $scope.estilos = undefined;
   $scope.influencias = undefined;
   $scope.instrumentos = undefined;
   $scope.markers = [];
   $scope.showFilters = false;

   $scope.map = {
      center: {latitude: 51.219053, longitude: 4.404418},
      zoom: 1,
      options: {scrollwheel: true, streetViewControl: false}
   };

   $scope.atualizarDados = function () {
      dataService.getEstilos().then(function (response) {
         $scope.estilos = response.data;
      });
      dataService.getInfluencias().then(function (response) {
         $scope.influencias = response.data;
      });
      dataService.getInstrumentos().then(function (response) {
         $scope.instrumentos = response.data;
         for (i in $scope.instrumentos) {
            $scope.instrumentos[i].nivelMax = 10;
            $scope.instrumentos[i].nivelMin = 1;
         }
      });
   };
   $scope.atualizarDados();

   $scope.atualizarMapa = function () {
      mapService.getMapMarkers($scope.filtros).then(function (response) {
         $scope.markers = response.data;
         for (i in $scope.markers) {
            $scope.markers[i].options = {label: {text: '10+', fontSize: '11px', fontFamily: 'Arial,sans-serif'}};
            if ($scope.markers[i].quantidade <= 10) {
               $scope.markers[i].options.label.text = $scope.markers[i].quantidade;
            }
         }
      });
   };

   $scope.gerarPDF = function () {
      var str = JSON.stringify($scope.filtros);
      $window.open('http://localhost/HubSound/api/acao.php?acao=reportMapUsers&filtros=' + encodeURI(str), '_blank');
   };
   $scope.gerarCSV = function () {
      var str = JSON.stringify($scope.filtros);
      $window.open('http://localhost/HubSound/api/acao.php?acao=csvMapUsers&filtros=' + encodeURI(str), '_blank');
   };

   uiGmapGoogleMapApi.then(function (map) {
      $scope.clickMarker = function (marker, evt, model) {
         mapService.getMapUsers(model.latitude, model.longitude, $scope.filtros).then(function (response) {
            var mapUsers = response.data;
            var contentString = "<div class=\"feed-activity-list\" ng-controller=\"MapController\">";
            for (i in mapUsers) {
               contentString += "<div class=\"feed-element\">" +
                  "<a href=\"/HubSound/#/index/profile?uid=" + mapUsers[i].id + "\" class=\"pull-left\">" +
                  "<img alt=\"image\" class=\"img-circle\" src=\"./api/acao.php?acao=getImageProfile&id=" + mapUsers[i].imagem + "\">" +
                  "<strong>&nbsp;" + mapUsers[i].nome + "</strong>" +
                  "</a>" +
                  "</div>";
            }
            contentString += "</div>";
            var infowindow = new google.maps.InfoWindow({
               content: contentString
            });
            infowindow.open(map, marker);
         });
      };
      $scope.atualizarMapa();
   });

   $scope.instrumentosSliderOptions = {
      floor: 1,
      ceil: 10,
      step: 1,
      minRange: 1,
      maxRange: 10,
      showTicks: true,
      showTicksValues: true,
      onChange: $scope.atualizarMapa,
      getPointerColor: function (value) {
         return '#18a689';
      }
   };
}

angular.module('hubsound')
   .controller('MainController', MainController)
   .controller('ProfileController', ProfileController)
   .controller('LoginController', LoginController)
   .controller('SingUpController', SingUpController)
   .controller('LikesController', LikesController)
   .controller('MapController', MapController)
   .controller('ChangeImageController', ChangeImageController)
   .controller('YouTubeController', YouTubeController)
   .controller('EditProfileController', EditProfileController);