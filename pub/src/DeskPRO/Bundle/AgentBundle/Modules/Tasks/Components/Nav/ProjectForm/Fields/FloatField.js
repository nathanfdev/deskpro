import React, { PropTypes } from 'react';

export class FloatField extends React.Component {

  static propTypes = {
    align: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired
  };

  render() {
    let children = this.props.children;
    if (children instanceof Array === false) {
      children = [children];
    }

    return (
      <div className={`dpw--popup-content-${this.props.align}`}>
        {children.map((child, index) => <div className="dpw-popup-content-item" key={index}>{child}</div>)}
      </div>
    );
  }
}
