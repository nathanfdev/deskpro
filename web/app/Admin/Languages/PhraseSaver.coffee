define ['DeskPRO/Util/Strings'], (Strings) ->
  class PhraseSaver
    constructor: (@Api, @$q) ->

    ###
      # Saves an array of phrases
      #
      # @param {Array} phrases
      # @return {promise}
    ###
    savePhrases: (langId, phrases) ->
      savePhrases = []

      for p in phrases
        setVal = p.set

        if setVal
          p.set = Strings.trim(p.set)

        if not setVal or p.set == "" or p.set == p.lang_default
          setVal = null

        if setVal == null
          p.set = p.lang_default

        savePhrases.push({
          name: p.id,
          phrase: setVal
        })

      deferred = @$q.defer()
      if not savePhrases.length
        deferred.resolve([])
        return deferred.promise

      phrases = []
      for n in savePhrases
        phrases.push n if 'user.general.helpdesk_by' != n.name

      @Api.sendPostJson("/langs/#{langId}/phrases", {
        phrases: phrases
      }).success(->
        deferred.resolve(savePhrases)
      ).error(->
        deferred.reject()
      )

      return deferred.promise