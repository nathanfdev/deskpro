import React, { PropTypes } from 'react';
import { TableGroupDivider } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

export class ListGroup extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    children: PropTypes.any
  };

  render() {
    const { title, children } = this.props;

    return (
      <tbody>
        {title && <TableGroupDivider title={title} />}
        {children}
      </tbody>
    );
  }
}
