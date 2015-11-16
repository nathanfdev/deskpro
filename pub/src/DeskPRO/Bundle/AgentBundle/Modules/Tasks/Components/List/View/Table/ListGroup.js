import React, { PropTypes } from 'react';

export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.any,
    children: PropTypes.node
  };

  render() {
    const { title, children } = this.props;

    return (
      <tbody>
        <tr className="divider">
          <td colSpan="5">
            <hr/>
            {title}
          </td>
        </tr>

        {children}
      </tbody>
    );
  }
}
