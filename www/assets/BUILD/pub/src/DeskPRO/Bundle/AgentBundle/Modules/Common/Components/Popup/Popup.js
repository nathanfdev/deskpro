import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class Popup extends React.Component {

  static propTypes = {
    indicator: PropTypes.string,
    additionalClassNames: PropTypes.string,
    children: PropTypes.node
  };

  render() {
    const { indicator, children, additionalClassNames } = this.props;

    return (
      <popup className={classNames(
        'sidebar-hover',
        additionalClassNames,
        {'hide-indicator': indicator === 'none'}
      )}>
        <div className="dpw--popup-main">
          {children}
        </div>
      </popup>
    );
  }
}
