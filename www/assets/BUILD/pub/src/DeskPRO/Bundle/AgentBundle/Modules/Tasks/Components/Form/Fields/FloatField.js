import React, { PropTypes, Children } from 'react';

export class FloatField extends React.Component {

  static propTypes = {
    align: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired
  };

  render() {
    const { children } = this.props;

    return (
      <div className={`dpw--popup-content-${this.props.align}`}>
        {Children.map(children, (child, index) =>
          <div className="dpw-popup-content-item" key={index}>
            {child}
          </div>
        )}
      </div>
    );
  }
}
