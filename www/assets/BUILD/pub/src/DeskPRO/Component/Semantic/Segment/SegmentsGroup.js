import React, { PropTypes } from 'react';
import classNames from 'classnames';

class SegmentsGroup extends React.Component {

  static propTypes = {
    children:   PropTypes.object.isRequired,
    classes:    PropTypes.arrayOf(PropTypes.string),
    raised:     PropTypes.bool,
    horizontal: PropTypes.bool,
    vertical:   PropTypes.bool,
    piled:      PropTypes.bool,
    stacked:    PropTypes.bool

  };

  static defaultProps = {
    classes:    [],
    raised:     false,
    horizontal: false,
    vertical:   false,
    piled:      false,
    stacked:    false
  };

  render() {
    const { stacked, raised, horizontal, vertical, piled, classes, children } = this.props;
    const additionaClasses = {
      raised,
      horizontal,
      vertical,
      piled,
      stacked
    };
    return (<div className={classNames('ui', 'segments', classes, additionaClasses)}>
      {children}
    </div>);
  }
}

export default SegmentsGroup;
