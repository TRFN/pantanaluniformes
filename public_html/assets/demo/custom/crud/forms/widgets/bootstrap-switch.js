//== Class definition

var BootstrapSwitch = function () {
    
    //== Private functions
    var demos = function () {
        // minimum setup
        $('[data-switch=true]').bootstrapSwitch();
    }

    return {
        // public functions
        init: function() {
            demos(); 
        }
    };
}();

LWDKInitFunction.addFN(function() {    
    BootstrapSwitch.init();
});