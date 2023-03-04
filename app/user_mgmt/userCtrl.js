var App = angular.module("go_solar.users", ['datatables', 'ui.bootstrap', 'ngMessages', 'xeditable']);

App.config(['$stateProvider', function ($stateProvider) {
    $stateProvider.state('users', {
        url: '/users',
        controller: "userCtrl",
        templateUrl: "user_mgmt/view_user.html",
        resolve: {
            checkLogin: checkLogin
        }
    }).state('view_user', {
        url: '/view_user/:id',
        controller: "viewUserCtrl",
        templateUrl: "user_mgmt/view_detail.html",
        resolve: {
            checkLogin: checkLogin
        }
    });
}]);

App.controller('userCtrl', ['$scope', '$uibModal', 'DTOptionsBuilder', 'DTColumnBuilder', 'userFactory', '$compile', 'base_url', '$state', function ($scope, $uibModal, DTOptionsBuilder, DTColumnBuilder, userFactory, $compile, base_url, $state) {
    $scope.dtInstance = {};

    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('stateSave', false)
        .withOption('ajax', {
            url: base_url + 'admin_api/getAppUsers',
            type: 'POST',
        })
        .withDataProp('data')
        .withOption('processing', true)
        .withOption('serverSide', true)
        .withPaginationType('full_numbers')
        .withOption('lengthMenu', [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ])
        .withOption('createdRow', function (row, data, dataIndex) {
            $compile(angular.element(row).contents())($scope);
        })
        .withOption('fnPreDrawCallback', function () {
            $scope.inProgress = true;
        }).withOption('fnDrawCallback', function () {
            $scope.inProgress = false;
        });

    $scope.dtColumns = [
        DTColumnBuilder.newColumn('id').withTitle('ID').notSortable(),
        DTColumnBuilder.newColumn('name').withTitle('Name').notSortable(),
        DTColumnBuilder.newColumn('email').withTitle('Email').notSortable(),
        DTColumnBuilder.newColumn('phone').withTitle('Phone').notSortable(),
        DTColumnBuilder.newColumn('created').withTitle('Date').notSortable(),
        DTColumnBuilder.newColumn(null).withTitle('Actions').notSortable()
            .renderWith(function (data, type, full, meta) {
                return '<button class="btn btn-info" ng-click="view_user(\'' + data.id + '\')"><i class="fa fa-eye"></i>&nbsp;View</button>';
            })
    ];
    $scope.msg = "";
    $scope.openAdd = function (size) {
        var uibModalInstance = $uibModal.open({
            templateUrl: "user_mgmt/add_user.html",
            controller: 'addUserCtrl',
            size: size
        });

        uibModalInstance.result.then(function () {
            $scope.dtInstance.reloadData(function () { }, false);
            $scope.msg = "User added successfully!!";

        });
    };

    $scope.view_user = function (id) {
        $state.go("view_user", { "id": id });
    }

    //Export Users in excelfile
    $scope.exportButtonText = "Export as Excel";
    $scope.exporting = false;

    $scope.exportUsers = function () {
        $scope.exportButtonText = "Processing...";
        $scope.exporting = true;
        userFactory.exportUsers().success(function (res) {
            $scope.exportButtonText = "Export as Excel";
            $scope.exporting = false;
            window.location = base_url + "referral/download?file=" + res.file;
        }).error(function (err) {
            $scope.exportButtonText = "Export as Excel";
            $scope.exporting = false;
            alert(err);
        })
    }

}]);

App.controller('addUserCtrl', ['$scope', '$uibModalInstance', 'userFactory', function ($scope, $uibModalInstance, userFactory) {
    $scope.user = {};
    $scope.addButtonText = "Add";
    $scope.adding = false;
    $scope.submit = function (form) {
        console.log(form);
        if (form.$valid && $scope.adding === false) {
            $scope.addButtonText = "Processing...";
            $scope.adding = true;
            userFactory.add_user($scope.user).success(function (res) {
                $uibModalInstance.close();
            }).error(function (err) {
                $scope.adding = false;
                $scope.addButtonText = "Add";
                console.log(err);
                $scope.err = err.message;
            })
        }
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    }

}]);

App.controller('viewUserCtrl', ['$rootScope', '$scope', 'userFactory', '$stateParams', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$compile', 'base_url', '$uibModal', '$state', function ($rootScope, $scope, userFactory, $stateParams, $timeout, DTOptionsBuilder, DTColumnBuilder, $compile, base_url, $uibModal, $state) {
    $scope.currentPower = 0;
    $scope.lastDayData = 0;
    $scope.lastMonthData = 0;
    $scope.lifeTimeData = 0;
    $scope.getUser = function () {
        // $rootScope.showLoader();
        userFactory.view_user($stateParams.id).success(function (res) {
            $scope.user = res.data;
            $scope.getPower();
        }).error(function (err) {
            console.log(err);
        })
    }
    $scope.getUser();
    $scope.getPower = function () {
        userFactory.getPower($scope.user.site_url,$scope.user.site_id, $scope.user.api_key).success(function (res) {
            $scope.data = res.data;

            $timeout(function () {
                // console.log($scope.data.overview.lifeTimeData.energy, typeof $scope.data.overview.lifeTimeData.energy)
                $scope.currentPower = $scope.data.overview.currentPower.power;
                $scope.lastDayData = ($scope.data.overview.lastDayData.energy) / 1000;
                $scope.lastMonthData = ($scope.data.overview.lastMonthData.energy) / 1000;
                $scope.lifeTimeData = ($scope.data.overview.lifeTimeData.energy / 1000000);
            }, 4000);

            // $rootScope.hideLoader();
        }).error(function (err) {
            console.log(err);
        })
    }
    $scope.error = "";

    $scope.update_user = function () {
        // console.log($scope.user.api_key, $scope.user.site_id);
        userFactory.updateUser($scope.user).success(function (res) {
            $scope.msg = res.message;
            $scope.getUser();
        }).error(function (err) {
            $scope.error = err.message;
            $timeout(function () { $scope.error = ""; }, 2000);
            $scope.getUser();
        })
    };

    //Referral Details
    $scope.R_disable = false;
    $scope.O_disable = false;
    /*-----------------Dattable setup-----------------*/
    $scope.dtInstance = {};
    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('stateSave', false)
        .withOption('ajax', {
            url: base_url + 'referral/getReferrals',
            type: 'POST',
            data: { "id": $stateParams.id, "type": "referral" }
        })
        .withDataProp('data')
        .withOption('processing', true)
        .withOption('serverSide', true)
        .withOption('searching', true)
        .withPaginationType('full_numbers')
        .withOption('lengthMenu', [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ])
        .withOption('createdRow', function (row, data, dataIndex) {
            $compile(angular.element(row).contents())($scope);
        })
        .withOption('fnPreDrawCallback', function () {
            $scope.inProgress = true;
        }).withOption('fnDrawCallback', function () {
            $scope.inProgress = false;
        });

    $scope.dtColumns = [
        DTColumnBuilder.newColumn('id').withTitle('ID'),
        DTColumnBuilder.newColumn('name').withTitle('Name'),
        DTColumnBuilder.newColumn('phone').withTitle('Phone'),
        DTColumnBuilder.newColumn('referrar').withTitle('Referred By'),
        DTColumnBuilder.newColumn('updated').withTitle('Date'),
        DTColumnBuilder.newColumn(null).withTitle('Status').notSortable()
            .renderWith(function (data, type, full, meta) {
                $scope.referBtn = '<button class="btn btn-primary" ng-click="view_referral(\'' + data.id + '\')">Referral</button>  '
                $scope.opportunityBtn = '<button class="btn btn-warning" ng-click="change_status(\'\',\'' + data.id + '\',\'opportunity\')"><i class="fa fa-eye"></i>&nbsp;Opportunity</button>  '

                if (data.user_type === 'opportunity') {
                    $scope.opportunityBtn = '<button class="btn btn-warning" ng-click="change_status(\'\',\'' + data.id + '\',\'opportunity\')" disabled><i class="fa fa-eye"></i>&nbsp;Opportunity</button>  '
                }

                return $scope.referBtn + $scope.opportunityBtn + '<button class="btn btn-success" ng-click="openSale(\'\',\'' + data.id + '\')"><i class="fa fa-eye"></i>&nbsp;Sales</button>';
            })
    ];
    /*------------------------------------------------*/

    /*Change user's status from referrals to opportunity*/
    $scope.change_status = function (size, id, status) {
        var uibModalInstance = $uibModal.open({
            templateUrl: "referral_mgmt/get_opportunityAmount.html",
            controller: 'oppoCtrl',
            size: size,
            resolve: {
                data: function () {
                    return id;
                }
            }
        });

        uibModalInstance.result.then(function () {
            $scope.dtInstance.reloadData(function () { }, false);
            $scope.msg = "User successfully marked as opportunity!!";
        });
    };
    /*------------------------------------------------*/
    /*Get sales amount on sales click*/
    $scope.openSale = function (size, id) {

        var uibModalInstance = $uibModal.open({
            templateUrl: "referral_mgmt/get_amount.html",
            controller: 'salesCtrl',
            size: size,
            resolve: {
                data: function () {
                    return id;
                }
            }
        });

        uibModalInstance.result.then(function () {
            $scope.dtInstance.reloadData(function () { }, false);
            $scope.msg = "User successfully marked as sales!!";
        });
    };
    /*------------------------------------------------*/

    /*View Referred user*/
    $scope.view_referral = function (data) {
        $state.go('view_referral', { "user": data });
    };

}]);

App.factory('userFactory', ['$http', 'base_url', function ($http, base_url) {
    var usr = {};

    usr.getAppUsers = function () {
        return $http.post(base_url + 'admin_api/getAppUsers');
    };

    usr.add_user = function (data) {
        return $http.post(base_url + 'admin_api/addUser', data);
    };

    usr.view_user = function (id) {
        return $http.post(base_url + 'admin_api/viewUser', { id: id });
    };

    usr.updateUser = function (data) {
        return $http.post(base_url + 'admin_api/updateUser', data);
    };

    usr.getPower = function (site_url, site_id, api_key) {
        return $http.post(base_url + 'admin_api/getPower', { "site_url": site_url, "site_id": site_id, "api_key": api_key });
    };

    usr.exportUsers = function () {
        return $http.post(base_url + 'admin_api/exportUsers');
    };

    return usr;
}]);
