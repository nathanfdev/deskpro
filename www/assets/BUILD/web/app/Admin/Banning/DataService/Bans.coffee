define [
  'Admin/Main/DataService/BaseListEdit',
  'Admin/Banning/IpBanEditFormMapper',
  'Admin/Banning/EmailBanEditFormMapper',
], (
  BaseListEdit,
  IpBanEditFormMapper,
  EmailBanEditFormMapper,
)  ->
  class Bans extends BaseListEdit
    @$inject = ['Api', '$q']
    @type = 'ip'

    ###
    #
    ###

    init: ->
      @setSubLists ['ip_bans', 'email_bans']
      @search_phrase = {
        ip_ban: '',
        email_ban: ''
        email_wildcard: false
      }

    ###
  #
  ###

    _doLoadList: ->

      deferred = @$q.defer()

      @Api.sendGet('/banning').success( (data) =>

        models = data.bans
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    ###
    #
    ###

    _doRefreshList: () ->

      deferred = @$q.defer()

      @Api.sendGet('/banning', {

        ip_ban_page: @pagination.ip_bans.page,
        email_ban_page: @pagination.email_bans.page,
        ip_ban_search_phrase: @search_phrase.ip_ban
        email_ban_search_phrase: @search_phrase.email_ban
        email_ban_wildcard: if @search_phrase.email_wildcard then 1 else 0

      }).success( (data) =>

        models = data.bans
        deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    ###
  # Returns object representing the search string defined via user UI
  ###

    getSearchPhrase: ->

      return @search_phrase

    ###
  # Sets type of ban that is used for create / update / delete operations
    #
  # @param {string} type
  ###

    setType: (type) ->

      @type = type
      @idProp = if @type == 'email' then 'banned_' + @type else 'id'

    ###
    # Get the form mapper
    #
    # @return {IpBanEditFormMapper|EmailBanEditFormMapper}
    ###

    getFormMapper: ->

      if @type == 'ip' then @formMapper = new IpBanEditFormMapper()
      if @type == 'email' then @formMapper = new EmailBanEditFormMapper()

      return @formMapper

    ###
    # Remove complete list
    #
    # @param {String} "email"|"ip"
    # @return {promise}
    ###

    deleteBanByType: (type) ->

      @Api.sendDelete('/banning_' + type)

    ###
  # Remove a model
  #
  # @param {Integer} id
  # @return {promise}
    ###

    deleteBanById: (id) ->

      promise = @Api.sendDelete('/banning_' + @type + '/' + window.encodeURIComponent(id)).success( =>
        @removeListModelById(id)
      )

      return promise

    ###
  # Get all data needed for the edit page
  #
  # @param {String} id
  # @return {promise}
    ###

    loadEditBanData: (id) ->

      deferred = @$q.defer()

      if id

        @Api.sendGet('/banning_' + @type + '/' + window.encodeURIComponent(id)).then( (result) =>

          data = {}
          data[@type + '_ban'] = result.data[@type + '_ban']
          data[@type + '_ban'].old_id = result.data[@type + '_ban'][@idProp]

          data.form = @getFormMapper().getFormFromModel(data)

          deferred.resolve(data)
        , ->
          deferred.reject()
        )

      else

        data = {}
        data[@type + '_ban'] = {}

        data.form = @getFormMapper().getFormFromModel(data)

        deferred.resolve(data)

      return deferred.promise

    ###
  # Saves a form model and merges model with list data
  #
  # @param {Object} model
  # @param {Object} formModel  The model representing the form
  # @return {promise}
    ###

    saveFormModel: (model, formModel) ->

      mapper = @getFormMapper()

      postData = mapper.getPostDataFromForm(formModel)

      sendData = {}
      sendData[@type + '_ban'] = postData
      
      if model['banned_' + @type]
        url = '/banning_' + @type + '/' + window.encodeURIComponent(if @type == 'email' then model['banned_' + @type] else model['id'])
        promise = @Api.sendPostJson(url, sendData).success((data) =>
          model['banned_' + @type] = data['banned_' + @type]
        )
      else
        promise = @Api.sendPutJson('/banning_' + @type, sendData).success( (data) =>
          model['banned_' + @type] = data['banned_' + @type]
        )

      promise.success( =>
        mapper.applyFormToModel(model, formModel)
        @mergeDataModel(model, null, @type + '_bans')
      )

      return promise