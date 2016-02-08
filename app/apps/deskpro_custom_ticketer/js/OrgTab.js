define(function() {
    return {
        init: function() {
            var $tab = this.getFragmentElement();
            var org = this.getFragment().meta.api_data;

            if (DP_CHECK_ORG_IS_OPERATOR(org)) {
                $tab.addClass('org-is-operator');
            } else {
                $tab.addClass('org-isnot-operator');
            }
        },

        getEl: function(id) {
            return this.getFragment().getEl(id);
        }
    }
});