import PropTypes from 'prop-types';
import React from 'react';

export class CollectionField extends React.Component {

  static propTypes = {
    title:    PropTypes.any,
    children: PropTypes.any
  };

  render() {
    const { children, title } = this.props;

    return (
      <div className="dpw--popup-content-of-three">
        <h1 className="dpw--popup-item-collection-title">
          {title}
        </h1>
        <div className="dpw--popup-item-collection">
          {children}
        </div>
      </div>
    );
  }
}
