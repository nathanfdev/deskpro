import React, { PropTypes } from 'react';

export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    children: PropTypes.node
  };

  render() {
    const { title, children } = this.props;

    return (
      <tbody>
        <tr className="divider">
          <td colSpan="4">
            <hr/>
            <span>{title}</span>
          </td>
        </tr>

        {children}
      </tbody>
    );
  }
}
