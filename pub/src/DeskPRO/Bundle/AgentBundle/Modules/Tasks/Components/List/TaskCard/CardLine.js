import React, { PropTypes } from 'react';

export class CardLine extends React.Component {

  static propTypes = {
    children: PropTypes.node.isRequired
  };

  render() {
    const left = this.props.children[0];
    const right = this.props.children[1];

    return (
      <div className="dpw--card-line">
        <div className="dpw--card-line-left">
          {left}
        </div>
        <div className="dpw--card-line-right">
          {right}
        </div>
      </div>
    );
  }
}
