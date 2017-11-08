import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class ControlItem extends React.Component {

  static propTypes = {
    disabled: PropTypes.bool,
    children: PropTypes.any,
    onClick:  PropTypes.func
  };

  onClick = event => {
    event.preventDefault();

    const { disabled, onClick } = this.props;
    if (!disabled) {
      onClick(event);
    }
  };

  render() {
    const { disabled, children } = this.props;

    return (
      <a
        href="#"
        className={classNames('dpdesignportal-chat-header-control-item', { disabled })}
        onClick={this.onClick}
      >
        {children}
      </a>
    );
  }
}
