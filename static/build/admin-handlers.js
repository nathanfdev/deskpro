Orb.createNamespace("DeskPRO.Admin.Departments");DeskPRO.Admin.Departments.AjaxSave=new Orb.Class({Extends:DeskPRO.ElementHandler,initPage:function(){var a=this;
this.el.delegate(".set-tickets-state, .set-chat-state","change",function(b){var c=$(this).closest("tr");a.saveFeatureState(c.data("department-id"))
})},registerChildHandler:function(c,a,b){switch(a){case"agent_selector":c.addEvent("updated",this.saveAgentPermissions.bind(this));
break;case"usergroup_selector":c.addEvent("updated",this.saveUsergroupPermissions.bind(this));break}},saveFeatureState:function(d){var c=$("tr.department-"+d,this.el);
var a=[];a.push({name:"chat",value:$(":checkbox.set-chat-state",c).is(":checked")?1:0});a.push({name:"tickets",value:$(":checkbox.set-tickets-state",c).is(":checked")?1:0});
var b=BASE_URL+"admin/departments/"+d+"/save-feature-state.json";$.ajax({url:b,type:"POST",dataType:"json",data:a})},saveAgentPermissions:function(d,c,e){var b=BASE_URL+"admin/departments/"+d+"/save-agents.json";
var a=[];Array.each(c,function(f){a.push({name:"agent_ids[]",value:f})});Array.each(e,function(f){a.push({name:"agent_team_ids[]",value:f})
});$.ajax({url:b,type:"POST",dataType:"json",data:a})},saveUsergroupPermissions:function(d,b){var c=BASE_URL+"admin/departments/"+d+"/save-usergroups.json";
var a=[];Array.each(b,function(e){a.push({name:"usergroup_ids[]",value:e})});$.ajax({url:c,type:"POST",dataType:"json",data:a})
}});Orb.createNamespace("DeskPRO.Admin.Departments");DeskPRO.Admin.Departments.AgentSelector=new Orb.Class({Extends:DeskPRO.ElementHandler,initPage:function(){var a=this;
this.department_id=this.el.data("department-id");var b=this.el;this.optionbox=new DeskPRO.UI.OptionBox({element:$("#optionbox_dep_"+this.department_id),trigger:this.el,onClose:function(e){var d=e.getCount("teams");
var c=e.getCount("agents");var f=[];if(c>0){f.push(c+" agents")}if(d>0){f.push(d+" teams")}if(!f.length){f=["No agents"]}b.text(f.join(", "));
a.fireEvent("updated",[a.department_id,e.getSelected("agents"),e.getSelected("teams"),a])}})},getHandlerName:function(){return"agent_selector"
}});Orb.createNamespace("DeskPRO.Admin.Departments");DeskPRO.Admin.Departments.UsergroupSelector=new Orb.Class({Extends:DeskPRO.ElementHandler,initPage:function(){var a=this;
this.department_id=this.el.data("department-id");var b=this.el;this.optionbox=new DeskPRO.UI.OptionBox({element:$("#optionbox_ug_"+this.department_id),trigger:this.el,onClose:function(d){var c=d.getCount("usergroups");
if(c>0){b.text(c+" usergroups")}else{b.text("No usergroups")}a.fireEvent("updated",[a.department_id,d.getSelected("usergroups"),a])
}})},getHandlerName:function(){return"usergroup_selector"}});