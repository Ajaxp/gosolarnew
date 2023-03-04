App.factory('learningFactory', ['$http', '$q', 'base_url', function($http, $q, base_url) {
    var learningFactory = {};
    /**
     * View Learning List
     */
    learningFactory.View = function() {
        return $http.get(base_url + "view_learning_list");
    };
    /**
     * View Category List
     */
    learningFactory.category_list = function() {
        return $http.get(base_url + "category_list");
    };
    /**
     * Add Learning List
     */
    learningFactory.add = function(data) {
        return $http.post(base_url, data);
    };
    /**
     * Edit View Learning List
     */
    learningFactory.editview = function(data) {
        return $http.post(base_url, data);
    };
    /**
     * Edit Learning List
     */
    learningFactory.edit = function(data) {
        return $http.post(base_url, data);
    };
    /**
     * Delete Learning List
     */
    learningFactory.delete = function(data) {
        return $http.post(base_url, data);
    };
    return learningFactory;
}]);

App.factory('AuthFactory', ['$http', 'base_url',function($http, base_url) {
    var data = {};

    data.fieldAvailable = function(field, value, id) {
        console.log(field);
        return $http.post(base_url + 'admin_api/checkField', field);
    }
    return data;
}]);
