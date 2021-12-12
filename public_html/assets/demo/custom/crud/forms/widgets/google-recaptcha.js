//== Class definition

var BootstrapTouchspin = function () {
    
    //== Private functions
    var demos = function () {
        // minimum setup
        //$('#m_timepicker_1, #m_timepicker_1_modal').timepicker();
    }

    return {
        // public functions
        init: function() {
            demos(); 
        }
    };
}();

LWDKInitFunction.addFN(function() {    
    BootstrapTouchspin.init();
});