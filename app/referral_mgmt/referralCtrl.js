var App = angular.module("go_solar.referrals", ['datatables', 'ui.bootstrap', 'ngMessages']);

App.config(['$stateProvider', function ($stateProvider) {
    $stateProvider.state('referrals', {
        url: '/referrals',
        controller: "referralCtrl",
        templateUrl: "referral_mgmt/referralList.html",
        resolve: {
            checkLogin: checkLogin
        }
    }).state('salesUsers', {
        url: '/sales_users',
        controller: "salesUserCtrl",
        templateUrl: "referral_mgmt/sales_users.html",
        resolve: {
            checkLogin: checkLogin
        }
    }).state('add_referral', {
        url: '/add_referral',
        controller: "addReferralCtrl",
        templateUrl: "referral_mgmt/add_referral.html",
        resolve: {
            checkLogin: checkLogin
        }
    }).state('view_referral', {
        url: '/view_referral/:user',
        controller: "viewReferralCtrl",
        templateUrl: "referral_mgmt/view_referral.html",
        resolve: {
            checkLogin: checkLogin
        }
    }).state('opportunities', {
        url: '/opportunities',
        controller: "opportunityCtrl",
        templateUrl: "referral_mgmt/opportunityList.html",
        resolve: {
            checkLogin: checkLogin
        }
    });
}]);

App.controller('referralCtrl', ['$rootScope', '$scope', '$uibModal', 'DTOptionsBuilder', 'DTColumnBuilder', 'referralFactory', '$compile', 'base_url', '$state', function ($rootScope, $scope, $uibModal, DTOptionsBuilder, DTColumnBuilder, referralFactory, $compile, base_url, $state) {
    $scope.R_disable = false;
    $scope.O_disable = false;
    /*-----------------Dattable setup-----------------*/
    $scope.dtInstance = {};
    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('stateSave', false)
        .withOption('ajax', {
            url: base_url + 'referral/getReferrals',
            type: 'POST',
            data: { "type": "referral" }
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

    //Export Referrals
    $scope.exportButtonText = "Export as Excel";
    $scope.exporting = false;
    $scope.exprtReferrals = function () {
        $scope.exportButtonText = "Processing...";
        $scope.exporting = true;
        referralFactory.exprtReferrals().success(function (res) {
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

//Cotroller for Amount popup
App.controller('salesCtrl', ['$scope', '$uibModalInstance', 'referralFactory', 'data', function ($scope, $uibModalInstance, referralFactory, data) {
    console.log(data);

    $scope.addButtonText = "Mark as sales";
    $scope.adding = false;

    $scope.submit = function (form) {
        if (form.$valid && $scope.adding === false) {
            $scope.user.id = data;
            $scope.addButtonText = "Processing...";
            $scope.adding = true;
            referralFactory.make_sold($scope.user).success(function (res) {
                $uibModalInstance.close();
            }).error(function (err) {
                $scope.error = "Something went wrong.!!";
            });
        }
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    }
}]);

//Opportunity amount popup
App.controller('oppoCtrl', ['$scope', '$uibModalInstance', 'referralFactory', 'data', function ($scope, $uibModalInstance, referralFactory, data) {
    $scope.submit = function (form) {
        $scope.user.id = data;
        referralFactory.change_status($scope.user, 'opportunity').success(function (res) {
            $uibModalInstance.close();
        }).error(function (err) {
            $scope.error = "Something went wrong.!!";
        })
    }

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    }
}]);

//Sales User Controller     
App.controller('salesUserCtrl', ['$scope', '$uibModal', 'DTOptionsBuilder', 'DTColumnBuilder', 'referralFactory', '$compile', 'base_url', '$state', function ($scope, $uibModal, DTOptionsBuilder, DTColumnBuilder, referralFactory, $compile, base_url, $state) {

    /*-----------------Dattable setup-----------------*/
    $scope.dtInstance = {};
    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('stateSave', false)
        .withOption('ajax', {
            url: base_url + 'referral/getReferrals',
            type: 'POST',
            data: { "type": "sales" }
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
        DTColumnBuilder.newColumn('updated').withTitle('Sales Date'),
        DTColumnBuilder.newColumn(null).withTitle('Action').notSortable()
            .renderWith(function (data, type, full, meta) {
                $scope.btn = '<button class="btn btnprimary" disabled> Added as APP user </button>';
                if (data.type !== 'app_user') {
                    $scope.btn = '<button class="btn btn-success" ng-click="openSAdd(\'\',\'' + data.id + '\',\'' + data.name + '\',\'' + data.phone + '\',\'' + data.email + '\')"><i class="fa fa-mobile"></i>&nbsp;&nbsp;Add as App user</button>';
                }
                // console.log(data);
                return $scope.btn;
            })
    ];
    /*------------------------------------------------*/

    /*Get sales amount on sales click*/
    $scope.openSAdd = function (size, id, name, phone, email) {        
        var f_l = name.split(" ");
        console.log(typeof email)
        var uibModalInstance = $uibModal.open({
            templateUrl: "referral_mgmt/add_user.html",
            controller: 'addSUserCtrl',
            size: size,
            resolve: {
                data: function () {
                    return {
                        "id": id,
                        "first_name": f_l[0],
                        "last_name": f_l[1],
                        "phone": phone,
                        "email": (email === "null") ? '' : email
                    }
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

//Add sales user to APP
App.controller('addSUserCtrl', ['$scope', '$uibModalInstance', 'data', 'referralFactory', function ($scope, $uibModalInstance, data, referralFactory) {
    // console.log(data);
    $scope.user = data;
    $scope.addButtonText = "Add";
    $scope.adding = false;

    $scope.submit = function (form) {
        if (form.$valid && $scope.adding === false) {
            $scope.addButtonText = "Processing...";
            $scope.adding = true;

            referralFactory.add_app_user($scope.user).success(function (res) {
                $uibModalInstance.close();
            }).error(function (err) {
                $scope.adding = false;
                console.log(err);
            })
        }
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    }
}]);

//Add Referalls By admin
App.controller('addReferralCtrl', ['$rootScope', '$scope', 'base_url', 'referralFactory', '$state', function ($rootScope, $scope, base_url, referralFactory, $state, $timeout) {
    $scope.url = base_url;
    $scope.user = {};
    $scope.error = "";
    $rootScope.RefMsg = "";
    $scope.addReferral = function (form) {
        if (form.$valid) {
            $rootScope.spinner = true;
            if ($scope.user.referred_by.originalObject) {
                $scope.user.referred_by = $scope.user.referred_by.originalObject.id;
            }

            referralFactory.addReferral($scope.user).success(function (res) {
                if (res.success === 0) {
                    $scope.error = res.message;
                    $rootScope.spinner = false;
                    $timeout(function () { $scope.error = ""; }, 3000);
                } else {
                    $rootScope.spinner = false;
                    $rootScope.RefMsg = "Referral added successfully.";
                    $state.go('referrals')
                }
            }).error(function (err) {
                $rootScope.spinner = false;
                $scope.error = err.message;
            })
        }

    }
}]);

//View Referral Detail
App.controller('viewReferralCtrl', ['$scope', 'referralFactory', '$stateParams', '$timeout', 'base_url', function ($scope, referralFactory, $stateParams, $timeout, base_url) {
    $scope.url = base_url;
    $scope.getReferral = function () {
        referralFactory.getReferral($stateParams.user).success(function (res) {
            $scope.user = res.data;
        }).error(function (err) {
            console.log(err);
        });
    }
    $scope.getReferral();

    $scope.update_user = function () {
        console.log($scope.user.referred_by);
        if ($scope.user.referred_by) {
            $scope.user.referred_by = $scope.user.referred_by.originalObject.id;
        }
        delete $scope.user.referrar;
        referralFactory.updateUser($scope.user).success(function (res) {
            $scope.msg = res.message;
            $scope.getReferral();
        }).error(function (err) {
            $scope.error = err.message;
            $timeout(function () { $scope.error = ""; }, 2000);
            $scope.getReferral();
        })
    };

}]);

App.controller('opportunityCtrl', ['$rootScope', '$scope', '$uibModal', 'DTOptionsBuilder', 'DTColumnBuilder', 'referralFactory', '$compile', 'base_url', '$state', function ($rootScope, $scope, $uibModal, DTOptionsBuilder, DTColumnBuilder, referralFactory, $compile, base_url, $state) {
    $scope.R_disable = false;
    $scope.O_disable = false;
    /*-----------------Dattable setup-----------------*/
    $scope.dtInstance = {};
    $scope.dtOptions = DTOptionsBuilder.newOptions()
        .withOption('stateSave', false)
        .withOption('ajax', {
            url: base_url + 'referral/getReferrals',
            type: 'POST',
            data: { "type": "opportunity" }
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
                $scope.referBtn = '<button class="btn btn-warning" ng-click="view_referral(\'' + data.id + '\')"><i class="fa fa-eye"></i>&nbsp;Opportunity</button>  '
                // $scope.opportunityBtn = '<button class="btn btn-warning" ng-click="change_status(\'\',\'' + data.id + '\',\'opportunity\')"><i class="fa fa-eye"></i>&nbsp;Opportunity</button>  '

                // if (data.user_type === 'opportunity') {
                //     $scope.opportunityBtn = '<button class="btn btn-warning" ng-click="change_status(\'\',\'' + data.id + '\',\'opportunity\')" disabled><i class="fa fa-eye"></i>&nbsp;Opportunity</button>  '
                // }

                return $scope.referBtn + '<button class="btn btn-success" ng-click="openSale(\'\',\'' + data.id + '\')"><i class="fa fa-eye"></i>&nbsp;Sales</button>';
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

//Factory
App.factory('referralFactory', ['$http', 'base_url', function ($http, base_url) {
    var rfr = {};

    rfr.change_status = function (data, status) {
        return $http.post(base_url + 'admin_api/change_status', { "data": data, "status": status });
    };

    rfr.make_sold = function (data) {
        return $http.post(base_url + 'admin_api/make_sold', data);
    };

    rfr.add_app_user = function (data) {
        return $http.post(base_url + 'admin_api/add_app_user', data);
    };

    rfr.addReferral = function (data) {
        return $http.post(base_url + 'admin_api/add_referral', { "add_referral_details": data });
    };

    rfr.getReferral = function (id) {
        return $http.post(base_url + 'admin_api/getReferral', { "id": id });
    };

    rfr.updateUser = function (data) {
        return $http.post(base_url + 'admin_api/updateUser', data);
    };

    rfr.exprtReferrals = function () {
        return $http.get(base_url + 'referral/csvExport');
    };
    return rfr;
}]);

App.directive('export', ['base_url', function (base_url) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            element.bind('click', function () {
                /*alert('yup');*/
                $.ajax({
                    type: "GET",
                    url: base_url + "referral/csvExport",
                    dataType: "json",
                    success: function (resultData) {
                        window.location = base_url + "referral/download?file=" + resultData.file;
                    }
                });
            });
        }
    };
}]);
