import PropTypes from 'prop-types';
import React, { Component } from 'react';
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
    const list = Immutable.List();

    this.state = {
      agents:      task.get('agents', list),
      teams:       task.get('teams', list),
      departments: task.get('departments', list)
    };
  }

  componentWillReceiveProps(props) {
    const { task = Immutable.Map() } = props;
    const list = Immutable.List();

    this.setState({
      agents:      task.get('agents', list),
      teams:       task.get('teams', list),
      departments: task.get('departments', list)
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
    this.props.onChange(Immutable.Map({ agents, teams, departments }));
  }

  onChange = (prop, value) => {
    this.setState({ [prop]: value });
    this.dirty = true;
  };

  render() {
    const { agents, teams, departments } = this.state;

    return (
      <Popup additionalClassNames="assign-form task-assign-form">
        <div className="dpw--popup-header">
          <i className="fa fa-tags" /> Assign to Task
        </div>

        <div className="dpw--popup-content">
          <Form>
            <AssignAgentContainer
              selected={agents}
              onChange={(value) => this.onChange('agents', value)}
            />

            <AssignTeamContainer
              selected={teams}
              onChange={(value) => this.onChange('teams', value)}
            />

            <AssignDepartmentContainer
              selected={departments}
              onChange={(value) => this.onChange('departments', value)}
            />
          </Form>
        </div>
      </Popup>
    );
  }
}
