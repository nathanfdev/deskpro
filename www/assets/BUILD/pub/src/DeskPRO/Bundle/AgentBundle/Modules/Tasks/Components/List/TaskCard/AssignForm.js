import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { Popup } from '../../../../Common/Components/Popup';
import { AssignForm as Form, AssignAgentContainer, AssignTeamContainer, AssignDepartmentContainer } from '../../../../Common/Components/Form';

export class AssignForm extends Component {

  static propTypes = {
    task:     PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    const { task = Immutable.Map() } = props;
    const set = Immutable.Set();

    this.state = {
      agents:      task.get('agents', set),
      teams:       task.get('teams', set),
      departments: task.get('departments', set)
    };
  }

  componentWillReceiveProps(props) {
    const { task = Immutable.Map() } = props;
    const set = Immutable.Set();

    this.setState({
      agents:      task.get('agents', set),
      teams:       task.get('teams', set),
      departments: task.get('departments', set)
    });
  }

  shouldComponentUpdate(props, state) {
    const { agents, teams, departments } = state;

    return !Immutable.is(this.state.agents, agents)
      || !Immutable.is(this.state.teams, teams)
      || !Immutable.is(this.state.departments, departments)
      ;
  }

  componentDidUpdate() {
    if (!this.dirty) return;

    this.dirty = false;
    const { agents, teams, departments } = this.state;
    this.props.onChange(Immutable.fromJS({
      agents, teams, departments
    }));
  }

  onChange = (prop, value) => {
    this.setState({ [prop]: value });
    this.dirty = true;
  };

  render() {
    const { agents, teams, departments } = this.state;

    return (
      <Popup additionalClassNames="assign-form">
        <div className="dpw--popup-header">
          <i className="fa fa-tags" /> Assign to Task
        </div>

        <div className="dpw--popup-content">
          <Form>
            <AssignAgentContainer selected={agents.toSet()}
              onChange={(value) => this.onChange('agents', value)}
              />

            <AssignTeamContainer selected={teams.toSet()}
              onChange={(value) => this.onChange('teams', value)}
              />

            <AssignDepartmentContainer selected={departments.toSet()}
              onChange={(value) => this.onChange('departments', value)}
              />
          </Form>
        </div>
      </Popup>
    );
  }
}
