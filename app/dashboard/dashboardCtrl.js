var App = angular.module('go_solar.dashboard', []);

App.config(['$stateProvider',function ($stateProvider) {
	$stateProvider.state('dashboard',{
		url:'/dashboard',
		controller: 'dashboardCtrl',
		templateUrl:'dashboard/dashboard.html',
		resolve:{
			 checkLogin: checkLogin
		}
	});		
}]);

App.controller('dashboardCtrl', ['$scope','$http','base_url', function ($scope,$http, base_url) {
	$http({
		"method":'GET',
		"url":base_url + 'admin_api/getDashboardData'
	}).success(function(res){
		$scope.data = res.data;
	}).error(function(err){
		$scope.error = err;
	})
}]);

