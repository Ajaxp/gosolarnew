var App = angular.module('go_solar.email_settings', []);
App.config(['$stateProvider', function($stateProvider) {
    $stateProvider.state('email_settings', {
        url: '/email_settings',
        controller: "emailSettingCtrl",
        templateUrl: "email_settings/email_settings.html",
        resolve: {
            checkLogin: checkLogin
        }
    });
}]);

App.controller('emailSettingCtrl', ['$scope', 'EmailSettingFactory','$timeout', function($scope, EmailSettingFactory,$timeout) {
    $scope.range = [{}];
    $scope.get = function() {
        EmailSettingFactory.get_settings().success(function(res) {
            $scope.range = res.data;
        }).error(function(err) {
            console.log(err);
        })
    }
    $scope.get();
    $scope.addNewEmail = function() {
        var newItemNo = $scope.range.length + 1;
        $scope.range.push({});
    };

    $scope.removeEmail = function() {
        var lastItem = $scope.range.length - 1;
        $scope.range.splice(lastItem);
    };
    $scope.save = function() {
        if ($scope.range == undefined || $scope.range[0] == undefined || $scope.range[0].email == undefined) {
            $scope.error = "Please enter email valid data.";
            $timeout(function() { $scope.error = ""; }, 3000);
        } else {
            EmailSettingFactory.save($scope.range).success(function(res) {
                $scope.msg = "Email settings saved successfully!!";
                $scope.error = "";
                $timeout(function() { $scope.msg = ""; }, 3000);
            	$scope.get();
            }).error(function(err) {
                $scope.error = "Something went wrong.";
            });
        }
    }

}]);

App.factory('EmailSettingFactory', ['$http', 'base_url', function($http, base_url) {
    var stg = {};

    stg.get_settings = function() {
        return $http.get(base_url + 'admin_api/get_admin_email_settings');
    };

    stg.save = function(data) {
        return $http.post(base_url + 'admin_api/save_admin_email_settings', data);
    };
    return stg;
}])
