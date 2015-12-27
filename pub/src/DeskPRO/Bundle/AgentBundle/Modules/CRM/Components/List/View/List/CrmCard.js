import React from 'react';

export class CrmCard extends React.Component {

  render() {
    const { element, selected } = this.props;

    return (
      <div className="card crm-card">
        ID:{element.id}<br/>
        {element.name}
      </div>
    );
  }
}
