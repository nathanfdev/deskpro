import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Tab extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string
  };

  render() {
    const { className, children } = this.props;
    return (<div className={classNames('ui bottom attached tab segment', className)}>
      {children}
    </div>);
  }
}
export default Tab;
