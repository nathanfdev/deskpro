import React, { PropTypes } from 'react';

class Field extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <div className="field">
        {this.props.children}
      </div>
    );
  }
}
export default Field;
