import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Popup extends React.Component {

  static propTypes = {
    indicator:            PropTypes.string,
    additionalClassNames: PropTypes.string,
    children:             PropTypes.node
  };

  render() {
    const { indicator, children, additionalClassNames } = this.props;

    return (
      <div className={classNames('dpw--popup-main', additionalClassNames, { 'hide-indicator': indicator === 'none' })}>
        {children}
      </div>
    );
  }
}

export { Popup };