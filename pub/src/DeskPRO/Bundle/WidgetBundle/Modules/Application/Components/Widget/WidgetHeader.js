import React, { PropTypes } from 'react';

export class WidgetHeader extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
