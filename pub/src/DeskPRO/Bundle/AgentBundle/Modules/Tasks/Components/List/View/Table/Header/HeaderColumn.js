import React, { PropTypes } from 'react';

export class HeaderColumn extends React.Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    order: PropTypes.string,
    currentOrder: PropTypes.string,
    currentDirection: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = () => {
    const { order, currentOrder, currentDirection, onChange } = this.props;
    onChange(order, order === currentOrder && currentDirection === 'desc' ? 'asc' : 'desc');
  };

  render() {
    const { title, currentOrder, order, currentDirection } = this.props;

    return (
      <th className="clickable-column" onClick={this.onChange}>
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
