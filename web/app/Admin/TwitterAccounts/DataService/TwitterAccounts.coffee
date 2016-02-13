define [
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TwitterAccounts/TwitterAccountEditFormMapper'
], (
  BaseListEdit,
  TwitterAccountEditFormMapper
)  ->
  class Admin_TwitterAccounts_DataService_TwitterAccounts extends BaseListEdit
    @$inject = ['Api', '$q']

    _doLoadList: ->
      deferred = @$q.defer()

      @Api.sendGet('/twitter_accounts').success( (data) =>

        models = data.twitter_accounts
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    ###
      # Remove a model
      #
      # @param {Integer} id twitter_account id
      # @return {promise}
    ###
    deleteTwitterAccountById: (id) ->

      promise = @Api.sendDelete('/twitter_accounts/' + id).then(=>
        @removeListModelById(id)
      )

      return promise

    ###
       # Get the form mapper
       #
       # @return {TwitterAccountEditFormMapper}
    ###
    getFormMapper: ->

      if @formMapper then return @formMapper
      @formMapper = new TwitterAccountEditFormMapper()
      return @formMapper

    ###
      # Get all data needed for the edit page
      #
      # @param {Integer} id twitter_account id
      # @return {promise}
    ###
    loadEditTwitterAccountData: (id) ->

      deferred = @$q.defer()

      if id

        @Api.sendGet('/twitter_accounts/' + id).then( (result) =>

          data = {}
          data.twitter_account = result.data.twitter_account
          data.all_agents = result.data.twitter_account.all_agents

          data.form = @getFormMapper().getFormFromModel(data)

          deferred.resolve(data)
        , ->
          deferred.reject()
        )

      else

        data = {}
        data.twitter_account = {
          id: null,
          verified: false,
          user: {
            profile_image_url: '',
            name: '',
            screen_name: '',
            agents: {}
          }
        }

        data.form = @getFormMapper().getFormFromModel(data)

        deferred.resolve(data)

      return deferred.promise


    ###
      # Saves a form model and merges model with list data
      #
      # @param {Object} model twitter_account model
        # @param {Object} formModel  The model representing the form
      # @return {promise}
    ###
    saveFormModel: (model, formModel) ->

      mapper = @getFormMapper()

      postData = mapper.getPostDataFromForm(formModel)

      if model.id
        promise = @Api.sendPostJson('/twitter_accounts/' + model.id, {twitter_account: postData})
      else
        promise = @Api.sendPutJson('/twitter_accounts', {twitter_account: postData}).success( (data) ->
          model.id = data.id
        )

      promise.success(=>
        mapper.applyFormToModel(model, formModel)
        @mergeDataModel(model)
      )

      return promise