import React, { PropTypes } from 'react';

export class CollectionField extends React.Component {

  static propTypes = {
    title:    PropTypes.string,
    children: PropTypes.node.isRequired
  };

  render() {
    const { children } = this.props;

    let title = this.props.title;
    if (children instanceof Array) {
      children.forEach((child, num) => {
        if (child.props && child.props.part === 'title') {
          title = child;
          delete children[num];
        }
      });
    }

    return (
      <div className="dpw--popup-content-of-three">
        <h1 className="dpw--popup-item-collection-title">{title}</h1>
        <div className="dpw--popup-item-collection">
          <div className="dpw--assignment-scrollable-container">
            {children}
          </div>
        </div>
      </div>
    );
  }
}
