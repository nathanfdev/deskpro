(function() {
  define(function() {
    /*
       # Description
       # -----------
       #
       # This is the trigger for an inline help content body to display. When it is clicked,
       # this element will fade away and the body will slide in. When the body is closed again,
       # the body will "minimise into" this switch and the switch will become visible again.
       #
       # The ID of these inline helps should be globally unique because their state is saved.
       #
       # Help State
       # ----------
       #
       # A help state can either be open, closed, or undefined. Undefined states default to being
       # open unless the default-state attribute is used to set it open.
       #
       # Example View
       # ------------
       # <div class="panel-heading">
    	#     <h3>
    	#         <span>Permissions</span>
    	#         <button class="btn inhelp-trigger" dp-inhelp-switch="admin.ticket_deps.edit.usergroup_perms"><i></i></button>
    	#     </h3>
    	# </div>
    	# <div class="panel-help" dp-inhelp-body="admin.ticket_deps.edit.usergroup_perms">
    	#     ...
    	# </div>
    */

    var Admin_Main_Directive_DpInhelpSwitch;
    Admin_Main_Directive_DpInhelpSwitch = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var bodyId, btnId, defaultState, id;
            defaultState = attrs['defaultState'] || 'open';
            id = '_ctrl_inhelp_state.' + attrs['dpInhelpSwitch'];
            bodyId = 'dp_inhelp_' + id.replace(/\./, '_');
            btnId = bodyId += '_bt';
            element.attr('id', btnId);
            scope.$watch(id, function(newVal) {});
          }
        };
      }
    ];
    return Admin_Main_Directive_DpInhelpSwitch;
  });

}).call(this);

/*
//@ sourceMappingURL=DpInhelpSwitch.js.map
*/