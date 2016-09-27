import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Segment extends React.Component {
  static propTypes = {
    children:  PropTypes.node,
    className: PropTypes.string
  };

  render() {
    const { className, children } = this.props;
    return (
      <div className={classNames('ui segment', className)}>
        {children}
      </div>
    );
  }
}
export default Segment;
