define ['jquery'], ($) ->
  ###
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
  #         <button class="btn inhelp-trigger" dp-inhelp-btn="admin.ticket_deps.edit.usergroup_perms"><i></i></button>
  #     </h3>
  # </div>
  # <div class="panel-help" dp-inhelp-body="admin.ticket_deps.edit.usergroup_perms">
  #     ...
  # </div>
  ###
  Admin_Main_Directive_DpInhelpBtn = ['InhelpState', '$rootScope', (InhelpState, $rootScope) ->
    return {
      restrict: 'A',
      link: (scope, element, attrs) ->

        id = attrs['dpInhelpBtn'].replace(/\./g, '_')
        scopedId = 'dp_ctrl_inhelp_state.' + id

        if not $rootScope.dp_ctrl_inhelp_state
          $rootScope.dp_ctrl_inhelp_state = {}

        defaultState = true
        if attrs['defaultState']? == 'closed'
          defaultState = false

        $rootScope.dp_ctrl_inhelp_state[id] = InhelpState.getState(id)
        if $rootScope.dp_ctrl_inhelp_state[id] == null
          $rootScope.dp_ctrl_inhelp_state[id] = defaultState

        bodyId = 'dp_inhelp_' + id
        btnId  = bodyId + '_btn';

        icon = angular.element('<i></i>')
        element.prepend(icon)

        element.attr('id', btnId).addClass('inhelp-trigger')

        #---
        # Change the elements current display state
        #---

        updateState = ->
          if $rootScope.dp_ctrl_inhelp_state?[id]
            element.fadeOut(200)
            $('#' + bodyId).slideDown(200);
          else
            element.fadeIn(200)
            $('#' + bodyId).slideUp(200);

          InhelpState.setState(id, $rootScope.dp_ctrl_inhelp_state?[id])


        #---
        # Respond to changes
        #---

        $rootScope.$watch(scopedId, (newVal) ->
          updateState(newVal)
        )

        element.on('click', (ev) ->
          ev.preventDefault()
          scope.$apply(->
            if $rootScope.dp_ctrl_inhelp_state?[id]
              $rootScope.dp_ctrl_inhelp_state[id] = false
            else
              $rootScope.dp_ctrl_inhelp_state?[id]  = true
          )
        )


        #---
        # Set the initial view state
        #---

        if $rootScope.dp_ctrl_inhelp_state[id]
          element.hide()
          $('#' + bodyId).show();
        else
          element.show()
          $('#' + bodyId).hide();
    }
  ]

  return Admin_Main_Directive_DpInhelpBtn