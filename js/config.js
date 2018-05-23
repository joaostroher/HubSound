function config($stateProvider, $urlRouterProvider, $ocLazyLoadProvider, uiGmapGoogleMapApiProvider) {
   $urlRouterProvider.otherwise("/index/me");

   uiGmapGoogleMapApiProvider.configure({
      key: 'AIzaSyCox-uYPu1zf0-77u2Dip0cd1KOIXi49Wc',
      libraries: 'places,visualization',
      language: 'pt-BR'
   });

   $ocLazyLoadProvider.config({
      // Set to true if you want to see what and when is dynamically loaded
      debug: false
   });

   $stateProvider
      .state('index', {
         abstract: true,
         url: "/index",
         templateUrl: "views/common/content.html",
         resolve: {
            loadPlugin: function ($ocLazyLoad) {
               return $ocLazyLoad.load([
                  {
                     files: ['js/plugins/sweetalert/sweetalert.min.js', 'css/plugins/sweetalert/sweetalert.css']
                  },
                  {
                     name: 'oitozero.ngSweetAlert',
                     files: ['js/plugins/sweetalert/angular-sweetalert.min.js']
                  }
               ]);
            }
         }
      })
      .state('index.map', {
         url: "/map?tipo",
         templateUrl: "views/map.html",
         controller: 'MapController',
         resolve: {
            loadPlugin: function ($ocLazyLoad) {
               return $ocLazyLoad.load([
                  {
                     files: ['js/plugins/sweetalert/sweetalert.min.js', 'css/plugins/sweetalert/sweetalert.css']
                  },
                  {
                     insertBefore: '#loadBefore',
                     name: 'localytics.directives',
                     files: ['css/plugins/chosen/chosen.css', 'js/plugins/chosen/chosen.jquery.js', 'js/plugins/chosen/chosen.js']
                  },
                  {
                     name: 'rzModule',
                     files: ['js/plugins/rzslider/rzslider.min.js', 'css/plugins/rzslider/rzslider.min.css']
                  }
               ]);
            }
         }
      })
      .state('index.me', {
         url: "/me",
         templateUrl: "views/profile.html",
         controller: 'ProfileController',
         resolve: {
            loadPlugin: function ($ocLazyLoad) {
               return $ocLazyLoad.load([
                  {
                     name: 'youtube-embed',
                     files: ['js/plugins/youtube/angular-youtube-embed.min.js','https://www.youtube.com/iframe_api']
                  }
               ])
            }
         }
      })
      .state('index.profile', {
         url: "/profile?uid",
         templateUrl: "views/profile.html",
         controller: 'ProfileController',
         resolve: {
            loadPlugin: function ($ocLazyLoad) {
               return $ocLazyLoad.load([
                  {
                     name: 'youtube-embed',
                     files: ['js/plugins/youtube/angular-youtube-embed.min.js','https://www.youtube.com/iframe_api']
                  }
               ])
            }
         }
      })
      .state('index.editprofile', {
         url: "/editprofile",
         templateUrl: "views/editprofile.html",
         controller: 'EditProfileController',
         resolve: {
            loadPlugin: function ($ocLazyLoad) {
               return $ocLazyLoad.load([
                  {
                     files: ['https://maps.googleapis.com/maps/api/js?key=AIzaSyCox-uYPu1zf0-77u2Dip0cd1KOIXi49Wc&signed_in=true&libraries=places']
                  },
                  {
                     name: 'google.places',
                     files: ['js/angular-google-places-autocomplete/autocomplete.min.js', 'css/angular-google-places-autocomplete/autocomplete.min.css']
                  },
                  {
                     insertBefore: '#loadBefore',
                     name: 'localytics.directives',
                     files: ['css/plugins/chosen/chosen.css', 'js/plugins/chosen/chosen.jquery.js', 'js/plugins/chosen/chosen.js']
                  },
                  {
                     name: 'rzModule',
                     files: ['js/plugins/rzslider/rzslider.min.js', 'css/plugins/rzslider/rzslider.min.css']
                  },
                  {
                     name: 'ngImgCrop',
                     files: ['css/plugins/ngImgCrop/ng-img-crop.css', 'js/plugins/ngImgCrop/ng-img-crop.js']
                  },
                  {
                     name: 'ngFileUpload',
                     files: ['js/plugins/ng-file-upload/ng-file-upload.min.js']
                  }
               ]);
            }
         }
      })
      .state('index.createband', {
         url: "/createband",
         templateUrl: "views/editprofile.html",
         controller: 'EditProfileController',
         resolve: {
            loadPlugin: function ($ocLazyLoad) {
               return $ocLazyLoad.load([
                  {
                     files: ['https://maps.googleapis.com/maps/api/js?key=AIzaSyCox-uYPu1zf0-77u2Dip0cd1KOIXi49Wc&signed_in=true&libraries=places']
                  },
                  {
                     name: 'google.places',
                     files: ['js/angular-google-places-autocomplete/autocomplete.min.js', 'css/angular-google-places-autocomplete/autocomplete.min.css']
                  },
                  {
                     insertBefore: '#loadBefore',
                     name: 'localytics.directives',
                     files: ['css/plugins/chosen/chosen.css', 'js/plugins/chosen/chosen.jquery.js', 'js/plugins/chosen/chosen.js']
                  },
                  {
                     name: 'rzModule',
                     files: ['js/plugins/rzslider/rzslider.min.js', 'css/plugins/rzslider/rzslider.min.css']
                  },
                  {
                     name: 'ngImgCrop',
                     files: ['css/plugins/ngImgCrop/ng-img-crop.css', 'js/plugins/ngImgCrop/ng-img-crop.js']
                  },
                  {
                     name: 'ngFileUpload',
                     files: ['js/plugins/ng-file-upload/ng-file-upload.min.js']
                  }
               ]);
            }
         }
      })
      .state('login', {
         url: "/login",
         templateUrl: "views/login.html",
         controller: 'LoginController',
         data: {pageTitle: 'Login', specialClass: 'loginbg'}
      })
      .state('singup', {
         url: "/singup",
         templateUrl: "views/singup.html",
         controller: 'SingUpController',
         data: {pageTitle: 'Registro', specialClass: 'loginbg'}
      });
}

angular
   .module('hubsound')
   .config(config)
   .run(function ($rootScope, $state, userService, $location) {
      $rootScope.$state = $state;
      $rootScope.$on('$locationChangeStart', function () {
         if (($location.path() != '/login') && ($location.path() != '/singup')) {
            userService.userIsOn().then(
               function (response) {
                  if (!response.data.logado)
                     $location.path('/login');
               }, function () {
               $location.path('/login');
            });
         }
      });
   })
   .filter("asDate", function () {
      return function (input) {
         return new Date(input);
      }
   })
   .filter('newlines', function () {
      return function (text) {
         return text.replace(/\n/g, '<br/>');
      }
   });

