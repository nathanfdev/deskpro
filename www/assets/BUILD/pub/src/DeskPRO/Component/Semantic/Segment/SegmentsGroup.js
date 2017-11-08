import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class SegmentsGroup extends React.Component {

  static propTypes = {
    children:   PropTypes.oneOfType([PropTypes.object, PropTypes.array]).isRequired,
    className:  PropTypes.string,
    raised:     PropTypes.bool,
    horizontal: PropTypes.bool,
    vertical:   PropTypes.bool,
    piled:      PropTypes.bool,
    stacked:    PropTypes.bool
  };

  static defaultProps = {
    className:  '',
    raised:     false,
    horizontal: false,
    vertical:   false,
    piled:      false,
    stacked:    false
  };

  render() {
    const { stacked, raised, horizontal, vertical, piled, className, children } = this.props;
    const additionalClasses = {
      raised,
      horizontal,
      vertical,
      piled,
      stacked
    };
    return (<div className={classNames('ui', 'segments', className, additionalClasses)}>
      {children}
    </div>);
  }
}

export default SegmentsGroup;
