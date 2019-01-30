/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['angular'], angular =>
  function(DataService) {

    const agentGroups = [];
    const teams  = [];
    const userGroups = [];
    DataService.get('AgentGroups').all().then(_groups => _groups.map(group => agentGroups.push({value: group.id.toString(), label: group.title})));
    DataService.get('AgentTeams').all().then(_teams => _teams.map(team => teams.push({value: team.id.toString(), label: team.name})));
    DataService.get('UserGroups').all().then(_groups => _groups.map(function(group) { if ((group.sys_name !== 'everyone') && (group.sys_name !== 'registered')) { return userGroups.push({value: group.id.toString(), label: group.title}); } }));

    return {
      restrict: 'E',
      replace: true,
      scope: {
        ngModel: '=',
        title: '@title'
      },
      template: `\
<div>
    <a href="#" ng-click="$event.preventDefault(); addAction()">
      Add Action
    </a>
    <div ng-repeat="action in ngModel" style="">
      <select ng-model="action.type" ng-change="onChangeActionType(action)">
        <option value="AddToAgentGroup" ng-if="allowedActions['AddToAgentGroup']">
          Add to agent permission group
        </option>
        <option value="AddToTeam" ng-if="allowedActions['AddToTeam']">
          Add to agent team
        </option>
        <option value="AddToUserGroup" ng-if="allowedActions['AddToUserGroup']">
          Add to usergroup
        </option>
        <option value="AddToOrg" ng-if="allowedActions['AddToOrg']">
          Add to Organization
        </option>
        <option value="AddToOrgExpression" ng-if="allowedActions['AddToOrgExpression']">
          Add to Organization from value
        </option>
        <option value="AddLabel" ng-if="allowedActions['AddLabel']">
          Add label
        </option>
        <option value="AddLabelExpression" ng-if="allowedActions['AddLabelExpression']">
          Add label from value
        </option>
        <option value="MakeAnAdmin" ng-if="allowedActions['MakeAnAdmin']">
          Make agent an admin
        </option>
      </select>

      <select
        ng-if="action.type === 'AddToAgentGroup'"
        ng-model="action.data"
        ng-options="obj.value as obj.label for obj in agentGroups"
        ng-required="true"
        >
      </select>

      <select
        ng-if="action.type === 'AddToTeam'"
        ng-model="action.data"
        ng-options="obj.value as obj.label for obj in teams"
        ng-required="true"
        >
      </select>

      <select
        ng-if="action.type === 'AddToUserGroup'"
        ng-model="action.data"
        ng-options="obj.value as obj.label for obj in userGroups"
        ng-required="true"
        >
      </select>

      <input type="text"
             ng-model="action.data"
             ng-if="['AddToOrg', 'AddToOrgExpression', 'AddLabel', 'AddLabelExpression'].indexOf(action.type) !== -1"
             class="form-control"
             style="width: 70px;"
             />

      <label>
        <input type="checkbox" ng-model="action.filter_enabled" ng-change="changeFilter(action)" />
        only if
      </label>
      <input type="text" class="form-control" ng-model="action.filter" ng-if="action.filter_enabled" style="width: 70px;" />

      <a href="#" ng-click="$event.preventDefault(); removeAction(action)">
        x
      </a>
    </div>
</div>\
`,

      controller($scope) {
        $scope.ngModel = $scope.ngModel || [];
        $scope.onChangeActionType = action => action.data = null;
        $scope.addAction = function() {
          const action = $scope.defaultAction ? {type: $scope.defaultAction} : {};
          return $scope.ngModel.push(action);
        };
        $scope.removeAction = function(action) {
          const index = $scope.ngModel.indexOf(action);
          if (index !== -1) { return $scope.ngModel.splice(index, 1); }
        };
        return $scope.changeFilter = function(action) {
          if (!action.filter_enabled) { return action.filter = ''; }
        };
      },


      link($scope, $el, $attr) {
        let _actions;
        $scope.agentGroups = agentGroups;
        $scope.teams  = teams;
        $scope.userGroups = userGroups;

        const type = $scope.$parent.$eval($attr.type);
        if (type === 'agent') {
          _actions = ['AddToAgentGroup', 'AddToTeam', 'AddToUserGroup', 'MakeAnAdmin', 'AddLabel', 'AddLabelExpression'];
        }
        if (type === 'user') {
          _actions = ['AddToUserGroup', 'AddToOrg', 'AddToOrgExpression', 'AddLabel', 'AddLabelExpression'];
        }

        $scope.allowedActions = {};

        _actions.map(action => $scope.allowedActions[action] = true);
        if (_actions[0]) { return $scope.defaultAction = _actions[0]; }
      }
    };
  }
);
