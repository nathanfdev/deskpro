define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Main_Ctrl_MainPage extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Main_Ctrl_MainPage'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$rootScope', '$location', '$stateParams']

    init: ->
      if @$location.path() == '/license'
        @$scope.isBillingInterface = true
      else
        @$scope.isBillingInterface = false

      @$rootScope.$on('$locationChangeSuccess', =>
        if @$location.path() == '/license'
          @$scope.isBillingInterface = true
        else
          @$scope.isBillingInterface = false
          if @$location?.path().match(/^\/portal/) && @$stateParams.brandId && @$stateParams.brandId != 'new'
            @$scope.selectBrandId = @$stateParams.brandId
            @$scope.brandId = @$stateParams.brandId


        $('.dp-layout-appbody').scrollTop(0);
      )

      @settings = {
        apps_kb: true,
        apps_news: true,
        apps_downloads: true,
        apps_feedback: true,
        apps_guides: true,
        iface_portal: true
        portal_mode: 'publish'
      }
      @portalSettings = @DataService.get 'PortalGeneralSettings'

      @$scope.brandId = @$stateParams.brandId

      if !@$scope.brandId
        @$scope.brandId = 1

      @$scope.selectBrandId = @$scope.brandId

      @portalSettings.setBrandId(@$scope.brandId)

      @$scope.$watch('brand_id', =>
        @portalSettings.getSettings().then((s) => @settings = s)
      )

      @getBrands()

      @$scope.$on 'dp-update-brands', (e) =>
        @getBrands()
        @portalSettings.getSettings().then((s) => @settings = s)

      @$scope.$watch('Ctrl.portalSettings.version', =>
        @portalSettings.getSettings().then((s) => @settings = s)
      )

      return

    getBrands: ->
      @Api2.sendGet('brands').then (res) =>
        @$scope.brands = res.data.data
      @Api2.sendGet('brands/default').then (res) =>
        @$scope.default_brand = res.data.data

    changeBrand: ->
        if @$scope.selectBrandId == '-1'
          @$scope.brandId = 'new';
          @$state.go 'portal.setup', {brandId: 'new'}
        else if @$scope.selectBrandId
          @$scope.brandId = @$scope.selectBrandId
          if typeof @$stateParams.brandId != 'undefined'
            @$state.go 'portal.setup', {brandId: @$scope.selectBrandId}

  Admin_Main_Ctrl_MainPage.EXPORT_CTRL()