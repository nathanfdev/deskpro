define [
  'DeskPRO/Util/Util',
  'underscore'
], (
  Util,
  _
) ->
  class Admin_TwitterAccounts_TwitterAccountEditFormMapper

    ###
      #
    #
    ###

    getFormFromModel: (model) ->

      form = {}

      form.id = model.twitter_account.id
      form.verified = model.twitter_account.verified

      form.user = {}
      form.user.profile_image_url = model.twitter_account.user.profile_image_url
      form.user.name = model.twitter_account.user.name
      form.user.screen_name = model.twitter_account.user.screen_name
      form.user.agents = model.twitter_account.user.agents

      form.agents = model.all_agents

      form.selected_agents = {}

      ids = _.pluck(form.user.agents, 'id')

      for id in ids
        form.selected_agents[id] = true

      return form

    ###
      #
      #
    ###

    applyFormToModel: (model, formModel) ->

      model.id = formModel.id

    ###
      #
      #
    ###

    getPostDataFromForm: (formModel) ->

      postData = {}

      postData.id = formModel.id

      # instead of agents, resulting request should include persons

      postData.persons = []

      for own key, value of formModel.selected_agents
        if value
          agent = _.findWhere(formModel.agents, {id: parseInt(key)})
          postData.persons.push(agent.id) if agent

      return postData