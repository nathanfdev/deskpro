import PropTypes from 'prop-types';
import React from 'react';

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
