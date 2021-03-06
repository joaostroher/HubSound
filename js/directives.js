/**
 * INSPINIA - Responsive Admin Theme
 *
 */


/**
 * pageTitle - Directive for set Page title - mata title
 */
function pageTitle($rootScope, $timeout) {
   return {
      link: function (scope, element) {
         var listener = function (event, toState, toParams, fromState, fromParams) {
            // Default title - load on Dashboard 1
            var title = 'HubSound';
            // Create your own title pattern
            if (toState.data && toState.data.pageTitle)
               title = 'HubSound | ' + toState.data.pageTitle;
            $timeout(function () {
               element.text(title);
            });
         };
         $rootScope.$on('$stateChangeStart', listener);
      }
   }
}

/**
 * sideNavigation - Directive for run metsiMenu on sidebar navigation
 */
function sideNavigation($timeout) {
   return {
      restrict: 'A',
      link: function (scope, element) {
         // Call the metsiMenu plugin and plug it to sidebar navigation
         $timeout(function () {
            element.metisMenu();
         });
      }
   };
}

/**
 * iboxTools - Directive for iBox tools elements in right corner of ibox
 */
function iboxTools($timeout) {
   return {
      restrict: 'A',
      scope: true,
      templateUrl: 'views/common/ibox_tools.html',
      controller: function ($scope, $element) {
         // Function for collapse ibox
         $scope.showhide = function () {
            var ibox = $element.closest('div.ibox');
            var icon = $element.find('i:first');
            var content = ibox.find('div.ibox-content');
            content.slideToggle(200);
            // Toggle icon from up to down
            icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
            ibox.toggleClass('').toggleClass('border-bottom');
            $timeout(function () {
               ibox.resize();
               ibox.find('[id^=map-]').resize();
            }, 50);
         },
            // Function for close ibox
            $scope.closebox = function () {
               var ibox = $element.closest('div.ibox');
               ibox.remove();
            }
      }
   };
}

/**
 * iboxTools with full screen - Directive for iBox tools elements in right corner of ibox with full screen option
 */
function iboxToolsFullScreen($timeout) {
   return {
      restrict: 'A',
      scope: true,
      templateUrl: 'views/common/ibox_tools_full_screen.html',
      controller: function ($scope, $element) {
         // Function for collapse ibox
         $scope.showhide = function () {
            var ibox = $element.closest('div.ibox');
            var icon = $element.find('i:first');
            var content = ibox.find('div.ibox-content');
            content.slideToggle(200);
            // Toggle icon from up to down
            icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
            ibox.toggleClass('').toggleClass('border-bottom');
            $timeout(function () {
               ibox.resize();
               ibox.find('[id^=map-]').resize();
            }, 50);
         };
         // Function for close ibox
         $scope.closebox = function () {
            var ibox = $element.closest('div.ibox');
            ibox.remove();
         };
         // Function for full screen
         $scope.fullscreen = function () {
            var ibox = $element.closest('div.ibox');
            var button = $element.find('i.fa-expand');
            $('body').toggleClass('fullscreen-ibox-mode');
            button.toggleClass('fa-expand').toggleClass('fa-compress');
            ibox.toggleClass('fullscreen');
            setTimeout(function () {
               $(window).trigger('resize');
            }, 100);
         }
      }
   };
}

/**
 * minimalizaSidebar - Directive for minimalize sidebar
 */
function minimalizaSidebar($timeout) {
   return {
      restrict: 'A',
      template: '<a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="" ng-click="minimalize()"><i class="fa fa-bars"></i></a>',
      controller: function ($scope, $element) {
         $scope.minimalize = function () {
            $("body").toggleClass("mini-navbar");
            if (!$('body').hasClass('mini-navbar') || $('body').hasClass('body-small')) {
               // Hide menu in order to smoothly turn on when maximize menu
               $('#side-menu').hide();
               // For smoothly turn on menu
               setTimeout(
                  function () {
                     $('#side-menu').fadeIn(400);
                  }, 200);
            } else if ($('body').hasClass('fixed-sidebar')) {
               $('#side-menu').hide();
               setTimeout(
                  function () {
                     $('#side-menu').fadeIn(400);
                  }, 100);
            } else {
               // Remove all inline style from jquery fadeIn function to reset menu state
               $('#side-menu').removeAttr('style');
            }
         };
      }
   };
}

/**
 * fullScroll - Directive for slimScroll with 100%
 */
function fullScroll($timeout) {
   return {
      restrict: 'A',
      link: function (scope, element) {
         $timeout(function () {
            element.slimscroll({
               height: '100%',
               railOpacity: 0.9
            });

         });
      }
   };
}

/**
 * slimScroll - Directive for slimScroll with custom height
 */
function slimScroll($timeout) {
   return {
      restrict: 'A',
      scope: {
         boxHeight: '@'
      },
      link: function (scope, element) {
         $timeout(function () {
            element.slimscroll({
               height: scope.boxHeight,
               railOpacity: 0.9
            });

         });
      }
   };
}

function elastic($timeout) {
   return {
      restrict: 'A',
      link: function ($scope, element) {
         $scope.initialHeight = $scope.initialHeight || element[0].style.height;
         var resize = function () {
            element[0].style.height = $scope.initialHeight;
            if ((element[0].scrollHeight + 2) > parseInt($scope.initialHeight)) {
               element[0].style.height = "" + (element[0].scrollHeight + 2) + "px";
            }
         };
         element.on("input change", resize);
         $timeout(resize, 0);
      }
   };
}

/**
 *
 * Pass all functions into module
 */
angular
   .module('hubsound')
   .directive('pageTitle', pageTitle)
   .directive('sideNavigation', sideNavigation)
   .directive('iboxTools', iboxTools)
   .directive('minimalizaSidebar', minimalizaSidebar)
   .directive('iboxToolsFullScreen', iboxToolsFullScreen)
   .directive('fullScroll', fullScroll)
   .directive('slimScroll', slimScroll)
   .directive('elastic', ['$timeout', elastic])
   .directive('profileImage', function () {
      return {
         scope: {
            profileImage: "="
         },
         link: function (scope, element, attrs) {
            if ((scope.profileImage != null) && (scope.profileImage != ''))
               attrs.$set('src', './api/acao.php?acao=getImageProfile&id='+scope.profileImage)
            else
               attrs.$set('src', './api/acao.php?acao=getImageProfile');
            attrs.$set('alt', 'Imagem de Perfil');
            attrs.$set('width', '48px');
            attrs.$set('heigth', '48px');
            
            scope.$watch("profileImage", function (newValue, oldValue) {
               if ((newValue != null) && (newValue != ''))
                  attrs.$set('src', './api/acao.php?acao=getImageProfile&id='+newValue)
               else
                  attrs.$set('src', './api/acao.php?acao=getImageProfile');   
            });
         }
      };
   })
   .directive('toHtml', function () {
      return {
         restrict: 'A',
         link: function (scope, el, attrs) {
            el.html(scope.$eval(attrs.toHtml));
         }
      };
   })
   .directive("fileread", [function () {
         return {
            scope: {
               fileread: "="
            },
            link: function (scope, element, attributes) {
               element.bind("change", function (changeEvent) {
                  var reader = new FileReader();
                  reader.onload = function (loadEvent) {
                     scope.$apply(function () {
                        scope.fileread = loadEvent.target.result;
                     });
                  }
                  reader.readAsDataURL(changeEvent.target.files[0]);
               });
            }
         }
      }]);