import React, { PropTypes } from 'react';

export class FullField extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    children: PropTypes.node.isRequired
  };

  render() {
    const { title, children } = this.props;

    return (
      <div className="dpmw--popup-content-full">
        <h2 className="dpw--popup-item-section-title">{title}</h2>
        <div className="dpw--popup-form-container">
          {children}
        </div>
      </div>
    );
  }
}
