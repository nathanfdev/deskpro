import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Message extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    close:     PropTypes.bool,
    header:    PropTypes.string,
    className: PropTypes.string
  };

  getHeader = () => {
    if (this.props.header) {
      return (
        <div className="header">
          {this.props.header}
        </div>
      );
    }
    return null;
  };

  getCloseIcon = () => {
    if (this.props.close) {
      return <i className="close icon" />;
    }
    return null;
  };

  render() {
    const { children, className } = this.props;
    return (
      <div className={classNames('ui message', className)} >
        {this.getCloseIcon()}
        {this.getHeader()}
        {children}
      </div>
    );
  }
}
export default Message;
