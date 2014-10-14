Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'people-list';
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
        this.fixed_fields = ['id', 'name_with_title'];

		self.$scope = DeskPRO_Window.$scope.$new();
		self.$q = DeskPRO_Window.$q;
		self.$timeout = DeskPRO_Window.$timeout;
		self.$http = DeskPRO_Window.$http;

		DeskPRO_Window.ngModule.dpInjector.invoke(['$compile', function($compile) {
			self.wrapper.data('$ngControllerController', self);
			$compile(self.wrapper.contents())(self.$scope);
			self.initScope();
		}]);


		this.resultTypeId = this.meta.cache_id || 0;

		this.displayOptions = new DeskPRO.Agent.PageHelper.DisplayOptions(this, {
			prefId: 'people-filter',
			resultId: this.resultId,
			refreshUrl: this.meta.refreshUrl,
			isListView: (this.meta.viewType == 'list' ? true : false)
		});
		this.ownObject(this.displayOptions);

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

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			/*onCountChange: function(count) {
				var isOpen = self.massActionsMenu.isOpen();

				if (count > 0 && !isOpen) {
					self.massActionsMenu.open();
				} else if (count <= 0 && isOpen) {
					self.massActionsMenu.close();
				}
			}*/
		});
		this.ownObject(this.selectionBar);

		this.massActionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('.perform-actions-trigger:first', this.wrapper),
			menuElement: $('.actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var itemEl = $(info.itemEl);
				var menuEl = itemEl.parent();
				var action = itemEl.data('action');

				if (menuEl.is('.submenu')) {
					action = menuEl.data('action');
				}

				var postData = self.selectionBar.getCheckedFormValues('ids');
				var removeFromList = false;

				switch (action) {
					case 'delete':

						break;

					case 'add-to-organization':
						var id = itemEl.data('organization-id');
						if (!id) {
							return;
						}

						postData.push({
							name: 'organization_id',
							value: id
						});
						break;

					case 'del-from-organization':

						break;

					case 'add-to-usergroup':
						var id = itemEl.data('usergroup-id');
						if (!id) {
							return;
						}

						postData.push({
							name: 'usergroup_id',
							value: id
						});
						break;

					case 'del-from-usergroup':
						var id = itemEl.data('usergroup-id');
						if (!id) {
							return;
						}

						postData.push({
							name: 'usergroup_id',
							value: id
						});
						break;

					default:
						return;
						break;
				}

				$.ajax({
					url: BASE_URL + 'agent/feedback/filter/mass-actions/' + action,
					data: postData,
					type: 'POST',
					dataType: 'json',
					success: function(data) {
						if (removeFromList) {
							self.selectionBar.getChecked().parent().fadeOut('fast');
						} else {
							self.selectionBar.getChecked().each(function() {
								var name = $('.subject', $(this).parent());
								DeskPRO_Window.util.showSavePuff(name);
							});
						}

						self.selectionBar.checkNone();
					}
				});
			}
		});
		this.ownObject(this.massActionsMenu);

		this.enableHighlightOpenRows('person', 'person_id', 'article.person-');

		var opt = {
			resultIds: this.meta.peopleResultIds,
			perPage: this.meta.perPage || 50
		};

		this.wrapper.on('click', 'button.agent-confirm-approve', function(ev) {
			ev.preventDefault();
			var el = $(this);
			DeskPRO_Window.util.ajaxWithClientMessages({
				url: BASE_URL + 'agent/people/validate/approve',
				data: { 'people_ids[]': el.data('person-id') },
				success: function() {
					DeskPRO_Window.getMessageBroker().sendMessage('agent.person.confirmed', { person_id: el.data('person-id') });
					el.closest('.validation-row').remove();
					self.updateUi();
				}
			});
		});
		this.wrapper.on('click', 'button.agent-confirm-delete', function(ev) {
			ev.preventDefault();
			var el = $(this);
			DeskPRO_Window.util.ajaxWithClientMessages({
				url: BASE_URL + 'agent/people/validate/delete',
				data: { 'people_ids[]': el.data('person-id') },
				success: function() {
					DeskPRO_Window.getMessageBroker().sendMessage('agent.person.removed', { person_id: el.data('person-id') });
					el.closest('article.row-item').remove();
					self.updateUi();
				}
			});
		});

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.person.removed', function(info) {
			var row = self.wrapper.find('article.person-' + info.person_id);
			row.remove();
			self.updateUi();
		});
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.person.confirmed', function(info) {
			var row = self.wrapper.find('article.person-' + info.person_id);
			row.find('.validation-row').remove();
			self.updateUi();
		});

		this.addEvent('activate', this.fillListItems, this);
	},

	initScope: function(){
		var self = this,
			$scope = this.$scope;

		$scope.persons = this.meta.persons;
		$scope.displayFields = this.meta.displayFields;
        $scope.listType = 'list';
        $scope.switchViewType = function() {
            $scope.listType = 'list' === $scope.listType ? 'table' : 'list';
        };

		$scope.isFieldDisplayable = function(person, field) {
			switch (field) {
				case 'language':
					return !!person.language;
				case 'organization':
					return !!person.organization;
				case 'labels':
					return person.labels && person.labels.length > 0;
				case 'person_username':
					return person.person_username && person.person_username.length > 0;
				default:
					if (0 !== field.indexOf('person_fields')) return false;
					return !!person[field];
			}
		};

        $scope.getDisplayableFields = function() {
            var fields = [];
            self.fixed_fields.each(function(v){
                fields.push(v);
            });
            $scope.displayFields.each(function(v){
                if (fields.indexOf(v) > -1) return;
                fields.push(v);
            });
            return fields;
        };

        $scope.getFieldDisplayName = function(field){
            return (field.charAt(0).toUpperCase() + field.slice(1)).replace('_', ' ');
        };

		$scope.$watch('persons', this.fillListItems.bind(this));

		// sometimes $scope.persons won't apply (as we're working outside of digest loop most of time), so force it
		$scope.$safeApply();
	},

	fillListItems: function() {
		var self = this,
			$scope = this.$scope,
			routeTemplate = $scope.routes.person;

		if (!self.IS_ACTIVE) return;
		$scope.listItems.length = 0;

		$scope.persons.each(function(person){
			$scope.addListItem('person', 'person:'+person.id, person.name_with_title, routeTemplate.replace('0000', person.id));
		});
	},

	destroyPage: function() {
		if (this.$scope) {
			this.$scope.$destroy();
			this.$scope = null;
		}
	}
});
