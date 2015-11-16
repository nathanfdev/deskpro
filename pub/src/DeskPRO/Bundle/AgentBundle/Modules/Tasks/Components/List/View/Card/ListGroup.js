import React, { PropTypes } from 'react';

export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.any,
    children: PropTypes.node
  };

  render() {
    const { title, children } = this.props;

    return (
      <div>
        <div className="divider">
          <hr/>
          <h1>{title}</h1>
        </div>

        {children}
      </div>
    );
  }
}
