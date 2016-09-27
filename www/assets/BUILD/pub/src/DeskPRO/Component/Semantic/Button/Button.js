import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Button extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string,
    onClick:   PropTypes.func
  };

  render() {
    const { children, className } = this.props;
    return (
      <button
        className={classNames('ui button', className)}
        onClick={this.props.onClick}
      >
        {children}
      </button>
    );
  }
}
export default Button;
