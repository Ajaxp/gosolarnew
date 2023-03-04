var App = angular.module("go_solar", [
    'ngRoute',
    'ui.router',
    'ui.bootstrap',
    'xeditable',
    'angucomplete-alt',
    'go_solar.login',
    'go_solar.dashboard',
    'go_solar.users',
    'go_solar.salesman',
    'go_solar.installers',
    'go_solar.referrals',
    'go_solar.app_settings',
    'go_solar.email_settings'
]);

App.value("base_url", "http://localhost/Go_Solar_Api/index.php/");
// App.value("base_url", "http://localhost/w/go_solar/index.php/");
// App.value("base_url", "http://202.131.117.90:8009/GoSolar/index.php/");
// App.value("base_url", "http://www.gosolarportal.com/admin/index.php/");
// var checkLogin = "";
var checkLogin = ['$http', '$q', 'base_url', '$state', '$rootScope', function($http, $q, base_url, $state, $rootScope) {

    var defer = $q.defer();

    $http.get(base_url + 'admin_api/checkLogin').success(function(res) {
        // console.log(res);
        $rootScope.user = res.user;
        defer.resolve(res);
    }).error(function(err) {
        $state.go('login');
        defer.reject(err);
    });

    return defer.promise;
}];



App.config(['$stateProvider', '$urlRouterProvider', '$httpProvider', function($stateProvider, $urlRouterProvider, $httpProvider) {

    $httpProvider.defaults.useXDomain = true;
    delete $httpProvider.defaults.headers.common["X-Requested-With"];
    $urlRouterProvider.otherwise("/login");
    $httpProvider.defaults.headers.common = {};
    $httpProvider.defaults.headers.post = {};
    $httpProvider.defaults.headers.put = {};
    $httpProvider.defaults.headers.patch = {};

}]);
App.controller('rootCtrl', ['$rootScope', '$scope', '$http', '$state', 'base_url', '$timeout', '$document', function($rootScope, $scope, $http, $state, base_url, $timeout, $document) {
    $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
        // console.log(toState)
        $rootScope.curState = toState;
        //$rootScope.modName = $rootScope.curState.data.title;
    });

    $rootScope.bodyRef = angular.element($document[0].body);
    $rootScope.spinner = false;
    $rootScope.showLoader = function() {
        // console.log("-------Loader Show--------");
        $rootScope.spinner = true;
        $rootScope.bodyRef.addClass('ovh');
    }

    $rootScope.hideLoader = function() {
        // console.log("-------Loader Hide--------");
        $rootScope.spinner = false;
        $rootScope.bodyRef.removeClass('ovh');
    }



    $rootScope.logout = function() {
        $rootScope.loggedIn = false;
        $http.post(base_url + "admin_api/logout").success(function(res) {
            $state.go("login");
        }).error(function(err) {

        });

    };
}]);
