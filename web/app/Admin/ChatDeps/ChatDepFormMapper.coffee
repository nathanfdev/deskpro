define [
	'Admin/Main/Model/DepAgentPermMatrix'
	'DeskPRO/Util/Util'
], (
	DepAgentPermMatrix,
	Util
) ->
	class ChatDepFormMapper

		###
		#
		###

		getFormFromModel: (dep, depPerms, agents, agentgroups, usergroups) ->
			form = {
				title: '',
				user_title: '',
				parent_id: '0',
				enable_user_title: false
			}

			if dep.id
				form.title = dep.title

				if not Util.isBlank(dep.user_title)
					form.user_title = dep.user_title
					form.enable_user_title = true

				if not Util.isBlank(dep.parent_id)
					form.parent_id = dep.parent_id + ""

			matrix = new DepAgentPermMatrix()
			for group in agentgroups
				matrix.addGroup(group, [])
			for agent in agents
				matrix.addAgent(agent, [])

			matrix.initPerms(depPerms.agentgroups, depPerms.agents)
			form.agent_perms = matrix

			form.usergroup_perms = {}
			for u in usergroups
				form.usergroup_perms[u.id] = { full: false }

			if depPerms.usergroups
				for p in depPerms.usergroups
					if not form.usergroup_perms[p.usergroup_id]?
						form.usergroup_perms[p.usergroup_id] = {}

					form.usergroup_perms[p.usergroup_id][p.perm_name] = true

			return form

		###
 	#
		###

		getPostDataFromForm: (formModel) ->

			depData = {}

			depData.title           = formModel.title
			depData.parent          = formModel.parent_id || "0"
			depData.move_tickets_to = 'self'

			if Util.isBlank(depData.parent)
				depData.parent = null

			if formModel.enable_user_title
				depData.user_title = formModel.user_title

			permData = formModel.agent_perms.getPermsData()

			for own uid,usergroup of formModel.usergroup_perms
				if usergroup.full
					permData.push({
						usergroup_id: uid,
						name: 'full',
						value: 1
					})


			postData = {
				department: depData,
				permissions: permData
			}

			return postData

		###
		#
		###

		applyFormToModel: (dep, formModel) ->

			dep.title = formModel.title

			if not dep.display_order?
				dep.display_order = 0

			if Util.isBlank(formModel.parent_id)
				dep.parent_id = null
			else
				dep.parent_id = parseInt(formModel.parent_id)