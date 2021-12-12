//== Class definition

var BootstrapSelect = function () {

    //== Private functions
    var demos = function () {
        // minimum setup
        $('.m_selectpicker').selectpicker();
    }

    return {
        // public functions
        init: function() {
            demos();
        }
    };
}();

LWDKInitFunction.addFN(function() {    
    BootstrapSelect.init();
});
