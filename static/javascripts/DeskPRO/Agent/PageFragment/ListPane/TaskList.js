Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TaskList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'task-list';
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
                this._initTaskProperty();
                this._initLabels();

                this.actionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('button.perform-actions-trigger:first', this.wrapper),
			menuElement: $('ul.actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var data = [];
				var lines = [];
				$('input.item-select:checked', this.wrapper).each(function() {
					lines.push($(this).parent().get(0));
					var typename = $(this).data('content-type');
					var id = $(this).data('content-id');

					data.push({
						name: 'ids[]',
						value: id
					});
				});

				if (!data.length) {
					return;
				}

				var action = $(info.itemEl).data('action');

				$.ajax({
					url: BASE_URL + 'agent/task/drafts/mass-actions/' + action,
					data: data,
					type: 'POST',
					dataType: 'json',
					context: this,
					success: function(data) {
//						if (data.affected) {
//							Array.each(data.affected, function(info) {
//								DeskPRO_Window.getMessageBroker().sendMessage('publish.drafts.list-remove', info);
//							}, this);
//						}
					}
				});
			}
		});
		this.ownObject(this.actionsMenu);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function(ev) {
				self.actionsMenu.open(ev);
			}
		});
		this.ownObject(this.selectionBar);




	},

        _initTaskProperty: function(){
            
            $('.calender').datepicker();
            
            $('.add-comment', this.wrapper).click(function(){                
                $(this).parents('article').find('article').find('li.new-note').toggle();
            });

            $('.add-label', this.wrapper).click(function(){
                $(this).parents('article').find('.task-label').toggle();
            });

            $('.add-due-date', this.wrapper).click(function(){ 
                $(this).parents('article').find('.task-due-date').toggle();
            });

            $('.add-delegate', this.wrapper).click(function(){
                $(this).parents('article').find('.task-delegate').toggle();

            });

            $('.add-public', this.wrapper).click(function(){
                $.ajax({
                    url: $(this).attr('href'),
                    type: 'POST',
                    context: this,                    
                    dataType: 'json',
                    success: function(data) {
                        $(this).parents('article').find('li.public-task').slideUp('slow');
                        $(this).parents('article').find('li.private-task').slideDown('slow');
                    }
		});
                
                return false;

            });
            $('.add-private', this.wrapper).click(function(){
                    $.ajax({
                    url: $(this).attr('href'),
                    type: 'POST',
                    context: this,
                    dataType: 'json',
                    success: function(data) {
                        $(this).parents('article').find('li.private-task').slideUp('slow');
                        $(this).parents('article').find('li.public-task').slideDown('slow');
                    }
		});
                return false;
            });
        },

        setVisibility: function(url){
            
            $.ajax({
                    url: url,//this.getMetaData('labelsSaveUrl'),
                    type: 'POST',
                    context: this,
                    //data: data,
                    dataType: 'json',
                    success: function(data) {

                    }
		});
                return false;

        },

        //#########################################################################
	//# Labels
	//#########################################################################

	_initLabels: function() {

		// Tags
		this.labelsList = $(".task-tags ul", this.wrapper);

		this.labelsInput = new DeskPRO.UI.LabelsInput({
			type: 'task',
			list: this.labelsList,
			onChange: this.saveLabels.bind(this)
		});
		this.ownObject(this.labelsInput);
	},

	saveLabels: function() {
		if (this._saveLabelsTimeout) {
			window.clearTimeout(this._saveLabelsTimeout);
		}

		this._saveLabelsTimeout = this._doSaveLabels.delay(2000, this);
	},

	_doSaveLabels: function() {
		var data = $(':input', this.labelsList).serializeArray();
                var url =   this.labelsList.parents('article').find('.task-tags').attr('title');
		$.ajax({
			url: url,//this.getMetaData('labelsSaveUrl'),
			type: 'POST',
			context: this,
			data: data,
			dataType: 'json',
			success: function(data) {

			}
		});
	},

	destroyPage: function() {

	}
});