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
    const emptyObject = Immutable.fromJS({});
    const task = props.task;

    this.state = {
      ...localState,

      agent: task.get('agents', emptyObject).toArray()[0],
      team: task.get('teams', emptyObject).toArray()[0],
      department: task.get('departments', emptyObject).toArray()[0]
    };
  }

  componentWillReceiveProps(nextProps) {
    const task = nextProps.task;
    const emptyObject = Immutable.fromJS({});

    this.setState({
      agent: task.get('agents', emptyObject).toArray()[0],
      team: task.get('teams', emptyObject).toArray()[0],
      department: task.get('departments', emptyObject).toArray()[0]
    });
  }

  onClick = (param, value, isActive)=> {
    if (isActive) {
      this.setState({ [param]: null });
    } else {
      this.setState({ [param]: value });
    }
  };

  onSubmit = event => {
    event.preventDefault();

    const submitData = Immutable.fromJS({
      agents: [this.state.agent],
      teams: [this.state.team],
      departments: [this.state.department]
    });
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
              <AgentsListContainer selected={this.state.agent}
                                   filter={this.state.quickFilter}
                                   selfAssign={this.onAssignSelf}
                                   onClick={this.onClick}/>
              <TeamsListContainer selected={this.state.team}
                                  filter={this.state.quickFilter}
                                  onClick={this.onClick}/>
              <DepartmentsListContainer selected={this.state.department}
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
