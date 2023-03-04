var App = angular.module("go_solar.login", ['ngMessages']);

App.config(['$stateProvider', function($stateProvider) {
    $stateProvider.state('login', {
        url: '/login',
        controller:'loginCtrl',
        templateUrl: "login/view.html"
    });
}]);

App.controller('loginCtrl', ['$scope','Login','$state', function ($scope, Login, $state) {
	$scope.usr={};
	$scope.error="";
	$scope.login = function(form){
		// console.log(form)
		if(form.$valid){
			Login.login($scope.usr).success(function(res){
				console.log(res)
				$state.go("dashboard");
				$scope.error="";
			}).error(function(err){
				form.$setPristine();
                form.$setUntouched();
				$scope.usr.password = "";
				$scope.error = err.message;
			})
		}
	}
}]);

App.factory('Login', ['$http','base_url',function ($http, base_url) {
	var data={};

	data.login = function(data){
		return $http.post(base_url+'admin_api/login',{"email":data.email,"password":data.password});
	};

	return data;	

}])