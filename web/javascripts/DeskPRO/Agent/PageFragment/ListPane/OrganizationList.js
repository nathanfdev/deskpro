Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.OrganizationList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'org-list';
		this.wrapper = null;
		this.contentWrapper = null;
		this.overlay = null;
		this.appendUrl = null;
		this.actionsBarHelper = null;
		this.resultTypeName = 'filter';
		this.resultTypeId = 0;
	},

	initPage: function(el) {

		var self = this;

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', '$rootScope', '$q', '$timeout', '$http', function($compile, $rootScope, $q, $timeout, $http) {
			self.$scope = $rootScope.$new();

			self.$scope.$safeApply = function(fn) {
				var phase = this.$root.$$phase;
				if(phase == '$apply' || phase == '$digest') {
					if(fn && (typeof(fn) === 'function')) {
						fn();
					}
				} else {
					this.$apply(fn);
				}
			};

			self.$q = $q;
			self.$timeout = $timeout;
			self.$http = $http;

			self.wrapper.data('$ngControllerController', self);
			$compile(self.wrapper.contents())(self.$scope);

			self.initScope();
		}]);

		this.resultTypeId = this.meta.cache_id || 0;

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'org-filter',
			resultId: this.resultId,
			refreshUrl: this.meta.refreshUrl
		});
		this.ownObject(this.displayOptions);

		this.enableHighlightOpenRows('organization', 'org_id', 'article.org-');

//		var opt = {
//			resultIds: this.meta.orgResultIds,
//			perPage: this.meta.perPage || 50
//		};
//		if (this.meta.viewType && this.meta.viewType == 'list') {
//			opt.resultRowSelector = 'tr.row-item';
//			opt.resultsContainer = $('.table-result-list table', el);
//			opt.navEl = $('.bottom-action-bar', el);
//		}
//		this.resultsHelper = new DeskPRO.Agent.PageHelper.Results(this, opt);
//		this.ownObject(this.resultsHelper);

//		delete this.meta.orgResultIds;

		// Sorting options
		var sortMenuBtn = $('.order-by-menu-trigger', this.wrapper).first();
		this.sortingMenu = new DeskPRO.UI.Menu({
			triggerElement: sortMenuBtn,
			menuElement: $('.order-by-menu', this.wrapper).first(),
			onItemClicked: function(info) {
				var item = $(info.itemEl);

				var prop = item.data('order-by')
				var label = item.text().trim();

				// Change the displayed label for some visual feedback
				$('.label', sortMenuBtn).text(label);

				var disOptWrap = self.displayOptions.getWrapperElement();
				var sel = $('select.sel-order-by', disOptWrap);
				$('option', sel).prop('selected', false);
				$('option.' + prop, sel).prop('selected', true);

				self.displayOptions.saveAndRefresh();
			}
		});
		this.ownObject(this.sortingMenu);
	},

	initScope: function(){
		var self = this,
			$scope = self.$scope;

		$scope.organizations = this.meta.organizations;
		$scope.displayFields = this.meta.displayFields;

		// todo separate controller
		$scope.pagination = {
			page: this.meta.page,
			perPage: this.meta.perPage,
			ids: this.meta.resultIds, // todo remove
			total: this.meta.resultsTotal,
			isLoading: false
		};
		$scope.Math = window.Math;

		$scope.isFieldDisplayable = function(org, field) {
			switch (field) {
				case 'members_count':
					return org.members_count === undefined ? false : true;
				case 'labels':
					return org.labels && org.labels.length > 0;
				default:
					if (-1 === field.indexOf('organization_fields')) return false;
					return !!org[field];
			}
		};

		// todo move to pagination controller
		$scope.fetchPage = function(page){
			var pag = $scope.pagination;
			if (pag.isLoading) return;
			if (page < 1 || page > Math.ceil(pag.total / pag.perPage)) return;

			pag.isLoading = true;
			self.$http({
				url: self.meta.fetchResultsUrl,
				method: 'GET',
				params: {
					'result_ids[]': pag.ids.slice((page - 1) * pag.perPage, (page - 1) * pag.perPage + pag.perPage),
					'display_fields[]': $scope.displayFields,
					page: page,
					view_type: 'json'
				}
			}).then(function(data){
				pag.isLoading = false;
				$scope.organizations.length = 0;
				if (!data.data) data.data = [];
				data.data.each(function(org){ $scope.organizations.push(org); });
				pag.page = page;
			}, function(){
				pag.isLoading = false;
			});
		};

		$scope.$watch('organizations', function(newVal, oldVal){
			$scope.$parent.listItems.length = 0;
			var routeTemplate = $scope.$parent.routes.organization;
			newVal.each(function(org){
				$scope.$parent.addListItem('organization:'+org.id, org.name, routeTemplate.replace('0000', org.id));
			});
		});

		// sometimes $scope.persons won't apply (as we're working outside of digest loop most of time), so force it
		$scope.$safeApply();
	}
});
