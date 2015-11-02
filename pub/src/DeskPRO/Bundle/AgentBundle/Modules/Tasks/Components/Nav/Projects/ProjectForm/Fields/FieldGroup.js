import React, { PropTypes } from 'react';

export class FieldGroup extends React.Component {

  static propTypes = {
    children: PropTypes.node.isRequired
  };

  render() {
    return (
      <div className="dpw--popup-content-line">
        {this.props.children}
      </div>
    );
  }
}
