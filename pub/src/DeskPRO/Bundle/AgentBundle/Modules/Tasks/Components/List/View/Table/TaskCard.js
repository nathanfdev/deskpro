import React, { PropTypes } from 'react';
import Moment from 'moment';

export class TaskCard extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    const { task } = this.props;

    return (
      <tr key={task.get('id')}>
        <td>
            <span className="checkbox">
              <i className="fa fa-check selected" />
            </span>
          <a href="#">{task.get('title')}</a>
        </td>
        <td>{task.get('project')}</td>
        <td>{task.get('date_due') ? Moment(task.get('date_due')).format('DD/MM/YY') : 'N/A'}</td>
        <td></td>
      </tr>
    );
  }
}
