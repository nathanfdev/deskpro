import React from 'react';
import { CrmCard } from './CrmCard';

export class CrmList extends React.Component {

  render() {
    return (
      <div>
        {this.props.elements.map((element, index) => <CrmCard key={index} feedback={element} />)}
      </div>
    );
  }

}