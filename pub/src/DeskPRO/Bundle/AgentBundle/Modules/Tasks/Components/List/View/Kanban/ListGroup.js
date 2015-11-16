import React, { PropTypes } from 'react';
import { ListGroupTitleContainer } from '../ListGroupTitleContainer';

export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.any,
    children: PropTypes.node
  };

  render() {
    const { title, children } = this.props;

    return (
      <div className="list">
        <h1 className="kanban-list-header">{title}</h1>

        {children}
      </div>
    );
  }
}
