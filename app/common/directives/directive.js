App.directive("fieldAvailable", ['$http', '$timeout', 'AuthFactory', function($http, $timeout, AuthFactory) {
    return {
        restrict: "A",
        require: 'ngModel',
        scope: false,
        link: function(scope, elem, attrs, ctrl) {
            var checkField = function(viewValue) {

                ctrl.$setValidity("available", true);

                if (ctrl.$valid) {          

                    ctrl.$setValidity("checking", false);

                    if (viewValue !== "" && typeof viewValue !== "undefined") {

                        AuthFactory.fieldAvailable({ field: attrs.fieldAvailable, value: viewValue, id: attrs.fieldId }).success(function(data, status, headers, config) {
                            ctrl.$setValidity("available", true);
                            return ctrl.$setValidity("checking", true);
                        }).error(function(data, status, headers, config) {
                            ctrl.$setValidity("available", false);
                            return ctrl.$setValidity("checking", true);
                        });
                    } else {
                        ctrl.$setValidity("available", false);
                        ctrl.$setValidity("checking", true);
                    }
                }
                return viewValue;
            };

            if (!attrs.afterLeave) {
                return ctrl.$parsers.push(checkField);
            } else {
                elem.on('blur', function() {
                    checkField(scope.$eval(attrs.ngModel));
                });
            }
        }
    };
}]);