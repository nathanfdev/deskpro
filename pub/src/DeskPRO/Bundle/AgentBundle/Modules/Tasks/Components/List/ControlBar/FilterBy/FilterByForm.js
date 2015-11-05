import React from 'react';
import { Popup, Header } from '../../../Form/index';

export class FilterByForm extends React.Component {

  render() {
    return (
      <Popup indicator="none">
        <Header>
          Filter
        </Header>
      </Popup>
    );
  }
}
