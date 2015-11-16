import React, { PropTypes } from 'react';
import Moment from 'moment';
import { BaseTaskCard, ProjectContainer } from '../../../TaskCard/index';
import { Project } from './Project';
import { AssigneeContainer } from './AssigneeContainer';
import { TableCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      selected: false
    };
  }

  render() {
    const { task, selected, onToggleSelected } = this.props;

    return (
      <tr key={task.get('id')}>
        <td>
          <TableCheckbox selected={selected} onClick={onToggleSelected} />
        </td>
        <td>
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
