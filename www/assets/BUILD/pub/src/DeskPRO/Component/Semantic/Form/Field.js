import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Field extends React.Component {
  static propTypes = {
    children: PropTypes.node,
    classes:  PropTypes.string
  };

  render() {
    const { children, classes } = this.props;
    return (
      <div className={classNames('field', classes)}>
        {children}
      </div>
    );
  }
}
export default Field;
