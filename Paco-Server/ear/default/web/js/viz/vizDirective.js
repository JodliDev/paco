pacoApp.directive('multipleInputsDropDown', [function () {

  return {
    restrict: 'E',
    templateUrl: 'partials/viz/multipleInputs.html',
    controller: 'VizCtrl',
    link: function (scope, element, attrs, controller) {
    }
  }
}]);

pacoApp.directive('singleInputDropDown', [function () {

  return {
    restrict: 'E',
    templateUrl: 'partials/viz/singleInput.html',
    controller: 'VizCtrl',
    link: function (scope, element, attrs, controller) {
    }
  }
}]);

pacoApp.directive('participantsDropDown', [ function () {

  return {
    restrict: 'E',
    templateUrl:'partials/viz/participantsControl.html',
    controller: 'VizCtrl',
    link: function (scope, element, attrs, controller) {

    }
  }
}]);

pacoApp.directive('typesDropDown', [ function () {

  return {
    restrict: 'E',
    template: '<md-input-container class="typesDropDown">'+
    '<label class="typesLabel">Types</label>'+
    '<md-select ng-model="selectedType"  md-on-close="getSelectedType()">'+
    '<md-optgroup label="Viz Types">'+
    '<md-option ng-repeat="type in vizTypes" ng-value="type">{{type}}</md-option>'+
    '</md-optgroup></md-select></md-input-container>',
    controller: 'VizCtrl',
    link: function (scope, element, attrs, controller) {

    }
  }
}]);

// pacoApp.directive('xyDropDown', [ function () {
//
//   return {
//     restrict: 'E',
//     templateUrl: 'partials/viz/xyPlotControls.html',
//     controller: 'VizCtrl',
//     link: function (scope, element, attrs, controller) {
//
//     }
//   }
// }]);

// pacoApp.directive('singleInputDropDown', [ function () {
//
//   return {
//     restrict: 'E',
//     template: '<md-input-container class="inputsDropDown">'+
//     '<label class="inputsLabel">Input</label>'+
//     '<md-select class="inputsSelect" ng-model="input_timeSeries">'+
//     '<md-optgroup label="Inputs">'+
//     '<md-option ng-repeat="input in groupInputs" ng-value="input">{{input}}<div class="resType"><span>{{getResponseType(input)}}</span></div></md-option>'+
//     '</md-optgroup></md-select></md-input-container>',
//     controller: 'VizCtrl',
//     link: function (scope, element, attrs, controller) {
//
//     }
//   }
// }]);

pacoApp.directive('createButton', [function () {

  return {
    restrict: 'E',
    template: '<md-input-container class="vizCreateButton">'+
    '<md-button class="md-raised" ng-click="createViz()">{{createText}}</md-button>'+
    '</md-input-container>',
     controller: 'VizCtrl', //Embed a custom controller in the directive
    link: function (scope, element, attrs, controller) {

    }
  }
}]);

pacoApp.directive('clearButton', [function () {
  return {
    restrict: 'E',
    template: '<md-input-container>'+
    '<md-button class="md-primary vizClearButton" ng-click="clearViz()">Clear</md-button>'+
    '</md-input-container>',
    controller: 'VizCtrl', //Embed a custom controller in the directive
    link: function (scope, element, attrs, controller) {

    }
  }
}]);

pacoApp.directive('dateRange',[function(){
  return {
    restrict: 'E',
    templateUrl: 'partials/viz/dateTimeControl.html',
    controller: 'VizCtrl',
    link: function (scope, element, attrs, controller) {

    }
  }

}]);

