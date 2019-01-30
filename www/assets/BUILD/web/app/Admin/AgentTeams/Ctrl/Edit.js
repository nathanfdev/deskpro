// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_AgentTeams_Ctrl_Edit extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.setAvatar = this.setAvatar.bind(this);
      this.selectIcon = this.selectIcon.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_AgentTeams_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$upload', '$http'];
    }

    init() {
      this.teamId = parseInt(this.$stateParams.id);
      this.enable_avatar = false;

      this.$scope.icon_image = null;
      this.$scope.$on('icon.selected', (e, path) => this.selectIcon(path));

    }

    initialLoad() {
      let promise;
      if (this.teamId) {
        promise = this.Api.sendDataGet({
          team: `/agent_teams/${this.teamId}`,
          agents: "/agents"
        });
      } else {
        promise = this.Api.sendDataGet({
          agents: "/agents"
        });
      }

      promise.then( res => {
        this.agents = res.data.agents.agents;

        if (this.teamId) {
          this.team = res.data.team.team;
        } else {
          this.team = {members: []};
        }

        this.setAvatar(this.team.avatar);

        // value=true on agents that are members
        const memberIds = this.team.members.map(x => x.id);
        return this.agents.map(function(x) { if (Array.from(memberIds).includes(x.id)) { return x.value = true; } });
      });
      return promise;
    }



    setAvatar(blob) {
      this.team.avatar = blob;
      if ((blob == null)) {
        this.$scope.icon_image = null;
        return this.enable_avatar = false;
      } else {
        this.$scope.icon_image = blob.thumbnail_url_50;
        return this.enable_avatar = true;
      }
    }



    onFileSelect(files) {
      this.$scope.uploading = true;
      const file = files[0];

      return this.$upload.upload({
        url: this.$http.formatApiUrl('/misc/upload'),
        data: { is_image: true },
        file
      }).success( data => {
        this.$scope.uploading = false;
        return this.setAvatar(data.blob);
      }).error( data => {
        this.$scope.uploading = false;
        return this.Growl.error((data != null ? data.error_message : undefined) || 'Error');
      });
    }



    selectIcon(image) {
      if ((image == null)) { setAvatar(null); }

      this.$scope.uploading = true;
      return this.Api.sendPostJson('/misc/upload', {path: image, is_image: true}).then(
        data => {
          this.$scope.uploading = false;
          return this.setAvatar(data.data.blob);
        },
        () => {
          return this.$scope.uploading = false;
      });
    }



    saveForm() {
      let p;
      const postData = {
        team: {
          name: this.team.name,
          person_ids: []
        }
      };

      if (this.enable_avatar) {
        postData.team.avatar = (this.team.avatar != null ? this.team.avatar.id : undefined) || null;
      } else {
        this.avatar = null;
      }

      for (let a of Array.from(this.agents)) {
        if (a.value) {
          postData.team.person_ids.push(a.id);
        }
      }

      if (this.teamId) {
        p = this.sendFormSaveApiCall('POST', `/agent_teams/${this.teamId}`, postData);
      } else {
        p = this.sendFormSaveApiCall('PUT', "/agent_teams", postData);
      }

      p.then( res => {
        this.Growl.success(this.getRegisteredMessage('saved_team'));

        if (this.teamId) {
          return this.getTeamListCtrl().renameTeamById(this.teamId, this.team.name);
        } else {
          this.teamId = res.data.team_id;
          this.getTeamListCtrl().addTeam({ id: this.teamId, name: this.team.name});
          return this.$state.go('agents.teams.edit', {id: this.teamId});
        }
      });
    }

    /*
      * Shows the copy settings modal
      */
    showDelete() {
      let inst;
      const deleteTeam = () => {
        const p = this.Api.sendDelete(`/agent_teams/${this.teamId}`);
        p.then(() => {
          this.getTeamListCtrl().removeTeamById(this.teamId);
          return this.$state.go('agents.teams');
        });
        return p;
      };

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('AgentTeams/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doDelete = function(options) {
            $scope.is_loading = true;
            return deleteTeam().then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }


    /*
      * Gets a reference to the parent list view which we need to update with the new details
    */
    getTeamListCtrl() {
      if ((this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined) != null) {
        return this.$scope.$parent.ListCtrl;
      } else {
        // mock since list isnt there yet
        return {
          addTeam() {  },
          removeTeamById() {  },
          renameTeamById() {  }
        };
      }
    }
  }
  Admin_AgentTeams_Ctrl_Edit.initClass();

  return Admin_AgentTeams_Ctrl_Edit.EXPORT_CTRL();
});