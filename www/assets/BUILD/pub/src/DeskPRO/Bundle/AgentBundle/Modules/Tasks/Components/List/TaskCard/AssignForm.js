import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { FieldGroup, Popup} from '../../../../Common/Components/Popup';
import {
  BaseForm,
  Header,
  FullField,
  FloatField,
  Unassign
} from '../../Form';
import { AgentsListContainer, TeamsListContainer, DepartmentsListContainer }
  from '../../../../Common/Components/Form/Lists';

import { QuickFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';

export class AssignForm extends BaseForm {
  static propTypes = {
    task: PropTypes.object.isRequired,
    onSubmit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    const localState = this.state;
    const task = props.task;
    let assign = {};
    if (task.get('agents')) {
      assign = { agent: task.get('agents').toArray()[0] };
    } else if (task.get('teams')) {
      assign = { team: task.get('teams').toArray()[0] };
    } else if (task.get('departments')) {
      assign = { department: task.get('departments').toArray()[0] };
    }
    this.state = {
      ...localState,
      assign: assign
    };
  }

  componentWillReceiveProps(nextProps) {
    const task = nextProps.task;
    let assign = {};
    if (task.get('agents')) {
      assign = { agent: task.get('agents').toArray()[0] };
    } else if (task.get('teams')) {
      assign = { team: task.get('teams').toArray()[0] };
    } else if (task.get('departments')) {
      assign = { department: task.get('departments').toArray()[0] };
    }
    this.state = {
      assign: assign
    };
  }

  onClick = (param, value, isActive)=> {
    if (isActive) {
      this.setState({ assign: {} });
    } else {
      this.setState({ assign: { [param]: value } });
    }
  };

  onSubmit = event => {
    event.preventDefault();

    const submitData = Immutable.fromJS(this.state.assign);
    this.props.onSubmit(submitData);
  };

  render() {
    const { task } = this.props;

    return (
      <Popup additionalClassNames="assign-form">
        <Header>Assign to Task</Header>

        <form>
          <div className="dpw--popup-content">
            <FieldGroup>
              <FloatField align="left">
                <QuickFilter value={this.state.quickFilter} onChange={this.onChangeQuickFilter}/>
              </FloatField>

              <FloatField align="right">
                <Unassign onClick={this.onUnassignAll}/>
              </FloatField>
            </FieldGroup>

            <FieldGroup>
              <AgentsListContainer selected={this.state.assign.agent}
                                   filter={this.state.quickFilter}
                                   selfAssign={this.onAssignSelf}
                                   onClick={this.onClick}/>
              <TeamsListContainer selected={this.state.assign.team}
                                  filter={this.state.quickFilter}
                                  onClick={this.onClick}/>
              <DepartmentsListContainer selected={this.state.assign.department}
                                        filter={this.state.quickFilter}
                                        onClick={this.onClick}/>
            </FieldGroup>

            <FieldGroup>
              <FullField>
                <button type="submit"
                        value="Save"
                        className="dpw--popup-button"
                        onClick={this.onSubmit}>
                  {task.get('id') ? 'Save' : 'Ok'}
                </button>
              </FullField>
            </FieldGroup>
          </div>
        </form>
      </Popup>
    );
  }
}
