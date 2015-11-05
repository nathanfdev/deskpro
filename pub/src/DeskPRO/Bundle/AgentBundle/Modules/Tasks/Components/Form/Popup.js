import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class Popup extends React.Component {

  static propTypes = {
    indicator: PropTypes.string,
    children: PropTypes.node
  };

  render() {
    const { indicator, children } = this.props;

    return (
      <div className={classNames('sidebar-hover', {'hide-indicator': indicator === 'none'})}>
        <div className="dpw--popup-main">
          {children}
        </div>
      </div>
    );
  }
}
