define ->
  Reports_Directive_DashboardStat = ['$state', ($state) ->
    return {
    restrict: 'E',
    replace: true,
    #//transclude: true,
    #//scope: {
    #//    dData: '@',
    #//    title: '@'
    #//},www/web/app/build/dashpoc/js/app/templates/stat.html
    template: """
      <div class="stat">
          <div id="stat-title"></div>
          <div id="stat-value"></div>
      </div>
    """
    link: (scope, element, attrs) ->
      title = attrs.title
      value = attrs.value

      el = $(element)
      t = el.find('#stat-title')
      el.find('#stat-title').html(title)
      el.find('#stat-value').html(value)


#      // dynamic handler position
      el = $(element)
      box = el.parent()
      listItem = box.parent()

      listItem.scroll () ->

        #//console.log(box.offset().top);
        #//console.log(listItem.offset().top);
        t = box.offset().top - 47 - listItem.offset().top;
        #//console.log('total = ' + t);

        resHandlers = listItem.find('.gridster-item-resizable-handler')

        resHandlers.each (index, element) ->
          h = $(this)
          c = 1 + t
        #//console.log('current = ' + c);
          h[0].style.bottom = "#{c}px"
    }
  ]

  return Reports_Directive_DashboardStat