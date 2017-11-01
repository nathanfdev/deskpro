import PropTypes from 'prop-types';
import React from 'react';
import Loader from 'react-loader';
import classNames from 'classnames';

export class SaveTaskButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func,
    submit:  PropTypes.bool
  };

  render() {
    const { onClick, submit } = this.props;

    return (
      <div className="dpw--single-card-mark-done hovered"
        onClick={onClick}
      >

        <div>
          <i className="fa fa-save" />
          <span>Save Task</span>
        </div>
      </div>
    );
  }
}
