import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class Popup extends React.Component {

  static propTypes = {
    indicator:            PropTypes.string,
    additionalClassNames: PropTypes.string,
    children:             PropTypes.node
  };

  render() {
    const { indicator, children, additionalClassNames } = this.props;

    return (
      <div className={classNames('sidebar-hover', additionalClassNames, { 'hide-indicator': indicator === 'none' })}>
        {children}
      </div>
    );
  }
}
