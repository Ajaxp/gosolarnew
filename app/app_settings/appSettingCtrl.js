var App = angular.module('go_solar.app_settings', []);
App.config(['$stateProvider', function($stateProvider) {
    $stateProvider.state('app_settings', {
        url: '/referral_settings',
        controller: "settingCtrl",
        templateUrl: "app_settings/app_setting.html",
        resolve: {
            checkLogin: checkLogin
        }
    });
}]);

App.controller('settingCtrl', ['$scope', 'SettingFactory','$timeout', function($scope, SettingFactory,$timeout) {
    $scope.range = [{}];
    $scope.get = function() {
        SettingFactory.get_settings().success(function(res) {
            $scope.range = res.data;
        }).error(function(err) {
            console.log(err);
        })
    }
    $scope.get();
    $scope.addNewChoice = function() {
        var newItemNo = $scope.range.length + 1;
        $scope.range.push({});
    };

    $scope.removeChoice = function() {
        var lastItem = $scope.range.length - 1;
        $scope.range.splice(lastItem);
    };
    $scope.save = function() {
        if ($scope.range[0].network_name == undefined || $scope.range[0].network_percentage == undefined) {
            $scope.error = "Levels cannot be empty.";
            $timeout(function() { $scope.error = ""; }, 3000);
        } else {
            SettingFactory.save($scope.range).success(function(res) {
                $scope.msg = "Settings saved successfully!!";
                $timeout(function() { $scope.msg = ""; }, 3000);
            	$scope.get();
            }).error(function(err) {
                $scope.error = "Something went wrong.";
            });
        }
    }

}]);

App.factory('SettingFactory', ['$http', 'base_url', function($http, base_url) {
    var stg = {};

    stg.get_settings = function() {
        return $http.get(base_url + 'admin_api/get_settings');
    };

    stg.save = function(data) {
        return $http.post(base_url + 'admin_api/save_settings', data);
    };
    return stg;
}])
