import React, { PropTypes } from 'react';

export class HeaderColumn extends React.Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    order: PropTypes.string,
    currentOrder: PropTypes.string,
    currentDirection: PropTypes.string
  };

  render() {
    const { title, currentOrder, order, currentDirection } = this.props;

    return (
      <th className="clickable-column">
        {title}
        {order && currentOrder === order &&
          <span>
            {currentDirection === 'desc'
              ? <i className="fa fa-caret-down"/>
              : <i className="fa fa-caret-up"/>
            }
          </span>
        }
      </th>
    );
  }
}
