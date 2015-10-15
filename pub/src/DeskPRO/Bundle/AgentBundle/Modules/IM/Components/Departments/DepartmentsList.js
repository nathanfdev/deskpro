import React, {Component, PropTypes} from 'react';
import { DepartmentsListItem } from './DepartmentsListItem';

export class DepartmentsList extends Component {

  static propTypes = {
    departments: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    handleClickParticipant: PropTypes.func.isRequired
  };

  render() {
    return (
      <ul className="im-list short">
        {
          this.props.departments.map((department, index) => {
            return (
              <DepartmentsListItem
                dispatch={this.props.dispatch}
                handleClickParticipant={this.props.handleClickParticipant}
                key={index}
                department={department}
              />);
          })
        }
      </ul>
    );
  }
}