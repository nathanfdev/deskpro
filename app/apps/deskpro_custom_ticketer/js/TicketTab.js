define(function() {
    return {
        init: function() {
            var $tab = this.getFragmentElement()
        },

        getEl: function(id) {
            return this.getFragment().getEl(id);
        }
    }
});