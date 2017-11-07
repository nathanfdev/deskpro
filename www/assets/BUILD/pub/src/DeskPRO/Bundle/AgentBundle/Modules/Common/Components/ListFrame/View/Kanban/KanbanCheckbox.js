import PropTypes from 'prop-types';
import React from 'react';

export class KanbanCheckbox extends React.Component {

  static propTypes = {
    onClick:  PropTypes.func,
    selected: PropTypes.bool
  };

  render() {
    const { selected, onClick } = this.props;

    return (
      <div className="card-checkbox" onClick={onClick}>
        <span className="checkbox">
          {selected && <i className="fa fa-check" />}
        </span>
      </div>
    );
  }
}
