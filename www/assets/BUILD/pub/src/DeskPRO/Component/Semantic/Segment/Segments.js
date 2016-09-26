import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Segments extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    classes:  PropTypes.string
  };

  render() {
    const { classes, children } = this.props;
    return (
      <div className={classNames('ui segments', classes)}>
        {children}
      </div>
    );
  }
}
export default Segments;
