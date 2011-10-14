Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.NewTask = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newtask';
	},

	initPage: function(el) {
		this.wrapper = el;
                this.getEl('save').click(this.doSavePost.bind(this));
                this._initTaskProtertiesSection();
                this. _initComponent();
	},
        doSavePost: function() {
           
            //var formData = $(this).parents('.new-task').find('input, select').serialize();
            var formData = $('form#newTaskForm').serializeArray();
            $.ajax({
                type: 'POST',
                data: formData,
                url: $('form#newTaskForm').attr('action'),
                datataType: 'json',
                success: function(data){
                    if (data.success) {
                        //DeskPRO_Window.runPageRoute('task:' + BASE_URL + 'agent/tasks/'+ data.task_id);
                        DeskPRO_Window.newTaskLoader.toggle();
                    } else{
                        alert('There was an error with the form');
                    }
                }
            });
            return false;
        },
        submit:function() {
            return false;
        },

        _initTaskProtertiesSection: function()
        {
            var self = this;
            this.getEl('gear_spn').click(function(ev){
                
                $('input, select').val('');
                $('.taskpropertiec-section').toggle();

                return false;
                
            }).bind(this);

            this.getEl('add_another_task').click(function(){
                $('.new-task').clone().appendTo('form#newTaskForm');

            }).bind(this);
        }
        ,
        _initComponent: function()
        {
            $('.calender').datepicker();
        },

	destroyPage: function() {
            this.fireEvent('destroy', [this]);
	}
});
