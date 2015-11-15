import React, { PropTypes } from 'react';
import Moment from 'moment';
import { Checkbox } from './Checkbox';
import { BaseTaskCard, ProjectContainer } from '../../../TaskCard/index';
import { Project } from './Project';
import { AssigneeContainer } from './AssigneeContainer';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      selected: false
    };
  }

  render() {
    const { task } = this.props;

    return (
      <tr key={task.get('id')}>
        <td>
          <Checkbox selected={this.state.selected} onToggle={this.onToggleSelect} />
          <a href="#">{task.get('title')}</a>
        </td>
        <td>
          <ProjectContainer project={task.get('project')}>
            <Project />
          </ProjectContainer>
        </td>
        <td>{task.get('date_due') ? Moment(task.get('date_due')).format('DD/MM/YY') : 'N/A'}</td>
        <td>
          <AssigneeContainer task={task} />
        </td>
      </tr>
    );
  }
}
