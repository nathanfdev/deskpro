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
	},
        doSavePost: function() {
<<<<<<< HEAD
=======
            var formData = $('form#newTaskForm').serializeArray();   
            $.ajax({
                type: 'POST',
                data: formData,
                url: $('form#newTaskForm').attr('action'),
                datataType: 'json',
                success: function(data){
                    alert(data);
                }
            });
>>>>>>> 28926c9... add new css file for task view
            return false;
        },
        submit:function() {
            return false;
        },

        _initTaskProtertiesSection: function()
        {
            var self = this;
            this.getEl('gear_spn').click(function(ev){
                //this.getEl('task_proterties_section').show();
                $('.taskpropertiec-section').toggle();
                return false;
            }).bind(this);
        }
});
