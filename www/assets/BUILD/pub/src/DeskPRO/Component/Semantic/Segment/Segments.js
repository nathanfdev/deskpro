import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Segments extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string
  };

  render() {
    const { className, children } = this.props;
    return (
      <div className={classNames('ui segments', className)}>
        {children}
      </div>
    );
  }
}
export default Segments;
