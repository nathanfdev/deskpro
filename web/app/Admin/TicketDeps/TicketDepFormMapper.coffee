define [
	'Admin/Main/Model/DepAgentPermMatrix'
	'DeskPRO/Util/Util'
], (
	DepAgentPermMatrix,
	Util
) ->
	class TicketDepFormMapper
		getFormFromModel: (dep, trigger, layouts, depPerms, agents, agentgroups, usergroups, email_accounts) ->
			form = {
				title: '',
				user_title: '',
				parent_id: '0',
				enable_user_title: false,
				default_layout: {},
				custom_layout: {},
				use_custom_layout: false,
				trigger_actions: {
					SetEmailAccount: {
						options: {
							email_account_id: '0'
						}
					},
					SendUserEmail: {
						enabled: false,
						options: {}
					}
				}
			}

			if dep.id
				form.title = dep.title

				if not Util.isBlank(dep.user_title)
					form.user_title = dep.user_title
					form.enable_user_title = true

				if not Util.isBlank(dep.parent_id)
					form.parent_id = dep.parent_id+''


			if email_accounts.length
				form.trigger_actions.SetEmailAccount.options.email_account_id = email_accounts[0].id+''

			if trigger and trigger.actions?.actions?.length
				for act in trigger.actions.actions
					if act.type == 'SetEmailAccount'
						form.trigger_actions.SetEmailAccount.options = act.options
					else if act.type == 'SendUserEmail'
						form.trigger_actions.SendUserEmail.enabled = true
						form.trigger_actions.SendUserEmail.options = act.options

						if ['helpdesk_name', 'site_name'].indexOf(form.trigger_actions.SendUserEmail.options.from_name) == -1
							form.trigger_actions.SendUserEmail.options.from_name_custom = form.trigger_actions.SendUserEmail.options.from_name
							form.trigger_actions.SendUserEmail.options.from_name = 'custom'

			if not form.trigger_actions.SendUserEmail.enabled
				form.trigger_actions.SendUserEmail.options = {
					template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
					from_name: 'helpdesk_name'
				}

			form.default_layout = {
				agent: layouts.default_layout.agent.fields,
				user:  layouts.default_layout.user.fields,
			}

			if layouts.custom_layout and layouts.use_custom_layout
				form.custom_layout = {
					agent: layouts.custom_layout.agent.fields,
					user:  layouts.custom_layout.user.fields,
				}
				form.use_custom_layout = true
			else
				form.custom_layout = Util.clone(form.default_layout, true)

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


		getPostDataFromForm: (formModel) ->
			depData = {}

			depData.title           = formModel.title
			depData.parent          = formModel.parent_id || "0"
			depData.email_gateway   = formModel.email_gateway_id || "0"
			depData.move_tickets_to = 'self'

			if Util.isBlank(depData.parent)
				depData.parent = null
			if Util.isBlank(depData.email_gateway)
				depData.email_gateway = null

			if formModel.enable_user_title
				depData.user_title = formModel.user_titl

			permData = formModel.agent_perms.getPermsData()
			for own uid,usergroup of formModel.usergroup_perms
				if usergroup.full
					permData.push({
						usergroup_id: uid,
						name: 'full',
						value: 1
					})

			trigger_actions = []
			email_account_id = parseInt(formModel.trigger_actions.SetEmailAccount?.options?.email_account_id || 0)
			if email_account_id
				trigger_actions.push({
					type: 'SetEmailAccount',
					options: {
						email_account_id: email_account_id
					}
				})
			if formModel.trigger_actions.SendUserEmail?.enabled
				options = formModel.trigger_actions.SendUserEmail.options

				trigger_actions.push({
					type: 'SendUserEmail',
					options: {
						template:  options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
						from_name: if options.from_name == 'custom' then (options.from_name_custom || '') else (options.from_name || ''),
						do_cc_users: true,
						from_account: 0
					}
				})

			postData = {
				department: depData,
				trigger_actions: trigger_actions,
				permissions: permData
			}

			return postData


		applyFormToModel: (dep, formModel) ->
			dep.title = formModel.title

			if not dep.display_order?
				dep.display_order = 0

			if Util.isBlank(formModel.parent_id)
				dep.parent_id = null
			else
				dep.parent_id = parseInt(formModel.parent_id)

			if formModel.use_custom_layout
				dep.has_layout = true
			else
				dep.has_layout = false