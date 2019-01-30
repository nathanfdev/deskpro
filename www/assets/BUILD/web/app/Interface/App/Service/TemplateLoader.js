define ->
  class TemplateLoader
    constructor: (@loadUrl, @$http, @$q) ->

    setBust: (val) ->
      @bust = val

    getLoadUrl: (views) ->
      qs = []

      for t in views
        qs.push('views[]=' + encodeURIComponent(t))

      if @bust
        qs.push(@bust)

      qs = qs.join('&')

      return @loadUrl + '?' + qs

    load: (views) ->
      d = @$q.defer()

      @$http({
        method: 'GET',
        url: @getLoadUrl(views)
      }).success( (data) ->
        d.resolve(data)
      , (data, status) ->
        d.reject(data, status)
      )

      return d.promise