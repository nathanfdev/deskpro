define [], ->
	class Admin_Usersources_Helper_UsersourceTypeDecider
		constructor: ->
			return

		decide: ($state) ->
			console.log "inside it"
			console.log $state
			if $state.includes('crm') then 'user' else if $state.includes('agents') then 'agent' else null

	return new Admin_Usersources_Helper_UsersourceTypeDecider()