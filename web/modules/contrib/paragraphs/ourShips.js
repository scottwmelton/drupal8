'use strict';

// Directive for the Our Ships section
// Retrieves content from the CMS

angular.module('seabourn.components.ourShips', [])

.component('ourShips', {
    templateUrl: 'app/components/ourShips/ourShips.tpl.html',
    controller: 'ourShipsController',
    bindings: {
        'data': '='
    }
})

.controller('ourShipsController', function($window) {
	// Fields
    var vm = this;

	// Functions
    vm.clickShip = function(route) {
        $window.open(route, '_self');
    };
});
