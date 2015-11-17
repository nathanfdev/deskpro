import React, { PropTypes } from 'react';
import Moment from 'moment';
import { Td, TdId, TdTitle } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { BaseTaskCard, ProjectContainer } from '../../../TaskCard/index';
import { Project } from './Project';
import { AssigneeContainer } from './AssigneeContainer';
import { TableCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    tableVisibleFields: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      selected: false
    };
  }

  render() {
    const { task, selected, onToggleSelected, tableVisibleFields } = this.props;
    const isVisible = type => tableVisibleFields.includes(type);

    return (
      <tr>
        <Td>
          <TableCheckbox selected={selected} onClick={onToggleSelected} />
        </Td>
        <TdId visible={isVisible('id')}>{task.get('id')}</TdId>
        <TdTitle visible={isVisible('title')}>{task.get('title')}</TdTitle>
        <Td visible={isVisible('project')}>
          <ProjectContainer project={task.get('project')}>
            <Project />
          </ProjectContainer>
        </Td>
        <Td visible={isVisible('date_due')}>
          {task.get('date_due') ? Moment(task.get('date_due')).format('DD/MM/YY') : 'N/A'}
        </Td>
        <Td visible={isVisible('assignee')}>
          <AssigneeContainer task={task} />
        </Td>
      </tr>
    );
  }
}
