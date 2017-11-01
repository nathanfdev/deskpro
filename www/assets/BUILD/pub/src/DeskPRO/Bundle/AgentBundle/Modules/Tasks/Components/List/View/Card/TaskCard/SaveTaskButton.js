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
      <div className="dpw--single-card-mark-done hovered" onClick={onClick}>

        <div className={classNames({ saving: submit })}>
          <i className="fa fa-save" />
          <span>Save Task</span>
        </div>

        <Loader loaded={!submit} color="white" opacity={0} width={2} />
      </div>
    );
  }
}
