import React, { PropTypes } from 'react';

export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    children: PropTypes.node
  };

  render() {
    const { title, children } = this.props;

    return (
      <div>
        <div className="divider">
          <hr/>
          <h1><span>{title}</span></h1>
        </div>

        {children}
      </div>
    );
  }
}
