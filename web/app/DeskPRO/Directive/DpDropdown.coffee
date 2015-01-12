define ->
  DeskPRO_Directive_DpDropdown = ['$rootScope', '$document', ($rootScope, $document) ->
    return {
      restrict: 'A',
      scope:
        dropdownId: "@dpDropdown"
        openerId: "@dpDropdownOpener"
        closerId: "@dpDropdownCloser"

      link: (scope, element) ->

        scope.visible = false

        closeDropdown = () ->
          scope.visible = false
          processDropdown()

        toggleDropdown = () ->
          scope.visible = !scope.visible
          processDropdown()

        processDropdown = () ->
          if scope.visible == true
            dropdown.show()
          else
            dropdown.hide()

        if scope.openerId?
          opener = element.find("##{scope.openerId}")
        else
          opener = element

        if scope.closerId?
          closer = element.find("##{scope.closerId}")
          closer.bind 'click', (event) ->
            event.stopPropagation()
            closeDropdown()

        opener.bind 'click', toggleDropdown
        dropdown = element.find("##{scope.dropdownId}")

        processDropdown()
        $document.bind 'click', (event) ->
          event.stopPropagation()
          target = angular.element event.target
          clickedSystem = element
            .find(event.target)
            .length > 0;



          if (clickedSystem)
            if target.attr('dp-dropdown-item')?
              closeDropdown()
            else
              scope.visible = true
              processDropdown()
          else
            closeDropdown()
    }
  ]

  return DeskPRO_Directive_DpDropdown