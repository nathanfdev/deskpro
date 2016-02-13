import React from 'react';

export class Detached extends React.Component {
  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
