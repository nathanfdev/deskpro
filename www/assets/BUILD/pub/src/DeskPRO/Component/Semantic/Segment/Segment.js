import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Segment extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    classes:  PropTypes.string
  };

  render() {
    const { classes, children } = this.props;
    return (
      <div className={classNames('ui segment', classes)}>
        {children}
      </div>
    );
  }
}
export default Segment;
