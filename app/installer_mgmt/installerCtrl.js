var App = angular.module("go_solar.installers", ['datatables', 'ui.bootstrap', 'ngMessages', 'xeditable','oitozero.ngSweetAlert']);
App.config(['$stateProvider', function ($stateProvider) {
        $stateProvider.state('installers', {
        url: '/installers',
        controller: "installerCtrl",
        templateUrl: "installer_mgmt/view_user.html",
        resolve: {
            checkLogin: checkLogin
        }
    }).state('view_installer', {
        url: '/view_installer/:id',
        controller: "viewInstallerCtrl",
        templateUrl: "installer_mgmt/view_detail.html",
        resolve: {
            checkLogin: checkLogin
        }
        
    }).state('view_job', {
        url: '/view_job/:installer_id/:user_id',
        controller: "viewJobCtrl",
        templateUrl: "installer_mgmt/view_job.html",
        resolve: {
            checkLogin: checkLogin
        }
    });
    }]);

App.controller('installerCtrl', ['$scope', '$uibModal', 'DTOptionsBuilder', 'DTColumnBuilder', 'installerFactory', '$compile', 'base_url', '$state','SweetAlert', function ($scope, $uibModal, DTOptionsBuilder, DTColumnBuilder, installerFactory, $compile, base_url, $state,SweetAlert) {
        $scope.dtInstance = {};

    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('stateSave', false)
        .withOption('ajax', {
            url: base_url + 'admin_api/getInstallers',
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
                $scope.viewSalesUserBtn = '<button class="btn btn-info" ng-click="view_installer(\'' + data.id + '\')"><i class="fa fa-eye"></i>&nbsp;View</button>';
                if(data.status == "1"){
                    $scope.statusBtn = '<button class="btn btn-danger" ng-click="deactivate_installer(\'' + data.id + '\')"><i class="fa fa-lock"></i>&nbsp;Deactivate</button>';
                } else {
                    $scope.statusBtn = '<button class="btn btn-success" ng-click="activate_installer(\'' + data.id + '\')"><i class="fa fa-unlock"></i>&nbsp;Activate</button>';
                }
                
                return $scope.viewSalesUserBtn+" "+$scope.statusBtn;
            })
    ];
    $scope.msg = "";
    $scope.openAdd = function (size) {
        var uibModalInstance = $uibModal.open({
            templateUrl: "user_mgmt/add_user.html",
            controller: 'addInstallerCtrl',
            size: size
        });

        uibModalInstance.result.then(function () {
            $scope.dtInstance.reloadData(function () { }, false);
            $scope.msg = "User added successfully!!";

        });
    };

    $scope.view_installer = function (id) {
        $state.go("view_installer", { "id": id });
    }

    $scope.deactivate_installer = function(id){
        SweetAlert.swal({
            title: "Are you sure?",
            text: "Do you want to deactivate this installer?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes",
            closeOnConfirm: false,
        },
        function(isConfirm) {
            if (isConfirm) {
                var sendData = JSON.stringify({"id":id,"status":"0"});
                installerFactory.changeUserStatus(sendData).success(function (res) {
                    SweetAlert.swal({
                        title: "Deactivated!",
                        text: "Installer deactivated successfuly.",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "Ok",
                    });
                    $scope.dtInstance.reloadData(function () { }, false);
                }).error(function (err) {
                    alert(err);
                })
            }
        });
    }

    $scope.activate_installer = function(id){
        SweetAlert.swal({
            title: "Are you sure?",
            text: "Do you want to activate this installer?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes",
            closeOnConfirm: false,
        },
        function(isConfirm) {
            if (isConfirm) {
                var sendData = JSON.stringify({"id":id,"status":"1"});
                installerFactory.changeUserStatus(sendData).success(function (res) {
                    SweetAlert.swal({
                        title: "Activated!",
                        text: "Installer activated successfuly.",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "Ok",
                    });
                    $scope.dtInstance.reloadData(function () { }, false);
                }).error(function (err) {
                    alert(err);
                })
            }
        });
    }

    //Export Users in excelfile
    $scope.exportButtonText = "Export as Excel";
    $scope.exporting = false;

    $scope.exportUsers = function () {
        $scope.exportButtonText = "Processing...";
        $scope.exporting = true;
        installerFactory.exportUsers().success(function (res) {
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



App.controller('viewInstallerCtrl', ['$rootScope', '$scope', 'installerFactory', '$stateParams', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$compile', 'base_url', '$uibModal', '$state', function ($rootScope, $scope, installerFactory, $stateParams, $timeout, DTOptionsBuilder, DTColumnBuilder, $compile, base_url, $uibModal, $state) {
    $scope.currentPower = 0;
    $scope.lastDayData = 0;
    $scope.lastMonthData = 0;
    $scope.lifeTimeData = 0;
    $scope.getUser = function () {
        // $rootScope.showLoader();
        installerFactory.view_user($stateParams.id).success(function (res) {
            $scope.user = res.data;
            $scope.getPower();
        }).error(function (err) {
            console.log(err);
        })
    }
    $scope.getUser();
    $scope.getPower = function () {
        installerFactory.getPower($scope.user.site_url,$scope.user.site_id, $scope.user.api_key).success(function (res) {
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
        installerFactory.updateUser($scope.user).success(function (res) {
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
            data: { "installer_id": $stateParams.id, "type": "sales" }
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
                $scope.viewSalesUserBtn = '<button class="btn btn-info" ng-click="view_job(\'' + data.contactInstaller + '\',\''+data.id+'\')"><i class="fa fa-eye"></i>&nbsp;View</button>';
                return $scope.viewSalesUserBtn;
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

    $scope.view_job = function(installer_id,user_id){
        $state.go("view_job", { "installer_id": installer_id,"user_id": user_id});
    }

}]);

App.controller('viewJobCtrl', ['$rootScope', '$scope', 'installerFactory', '$stateParams', '$timeout', 'DTOptionsBuilder', 'DTColumnBuilder', '$compile', 'base_url', '$uibModal', '$state', function ($rootScope, $scope, installerFactory, $stateParams, $timeout, DTOptionsBuilder, DTColumnBuilder, $compile, base_url, $uibModal, $state) {
    $scope.currentPower = 0;
    $scope.lastDayData = 0;
    $scope.lastMonthData = 0;
    $scope.lifeTimeData = 0;
    $scope.getJob = function () {
        // $rootScope.showLoader();
        installerFactory.view_job({"installer_id":$stateParams.installer_id,"user_id":$stateParams.user_id}).success(function (res) {
            if(res.data && res.data.length > 0){
                $scope.job = res.data[0];
            } else {
                $scope.job = {};
            }
            
            console.log("job detail",$scope.job);
        }).error(function (err) {
            console.log(err);
        })
    }
    $scope.getJob();
    
    $scope.error = "";

    $scope.update_job = function () {
        // console.log($scope.user.api_key, $scope.user.site_id);
        var sendData = $scope.job;
        delete sendData.sales_man;
        delete sendData.installer;
        
        installerFactory.updateJob(sendData).success(function (res) {
            $scope.msg = res.message;
            $scope.getJob();
        }).error(function (err) {
            $scope.error = err.message;
            $timeout(function () { $scope.error = ""; }, 2000);
            $scope.getJob();
        })
    };

    //Referral Details
    $scope.R_disable = false;
    $scope.O_disable = false;

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

}]);

App.factory('installerFactory', ['$http', 'base_url', function ($http, base_url) {
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

    usr.changeUserStatus = function (data) {
        return $http.post(base_url + 'admin_api/changeUserStatus', data);
    };

    usr.view_job = function (data) {
        return $http.post(base_url + 'referral/getJobDetail', data);
    };

    usr.updateJob = function (data) {
        return $http.post(base_url + 'referral/updateJobDetail', data);
    };
    
    
    return usr;
}]);
