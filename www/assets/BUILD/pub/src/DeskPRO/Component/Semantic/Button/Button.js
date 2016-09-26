import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Button extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    classes:  PropTypes.string,
    onClick:  PropTypes.func
  };

  render() {
    const { children, classes } = this.props;
    return (
      <button
        className={classNames('ui button', classes)}
        onClick={this.props.onClick}
      >
        {children}
      </button>
    );
  }
}
export default Button;
