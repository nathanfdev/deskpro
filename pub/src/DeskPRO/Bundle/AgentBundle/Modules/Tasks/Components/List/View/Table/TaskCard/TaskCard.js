import React, { PropTypes } from 'react';
import Moment from 'moment';
import { BaseTaskCard } from '../../BaseTaskCard';
import { Checkbox } from './Checkbox';

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
        <td>{task.get('project')}</td>
        <td>{task.get('date_due') ? Moment(task.get('date_due')).format('DD/MM/YY') : 'N/A'}</td>
        <td></td>
      </tr>
    );
  }
}
