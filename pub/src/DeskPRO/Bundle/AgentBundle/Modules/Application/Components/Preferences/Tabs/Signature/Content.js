import React from 'react';
import { SingleForm } from './Form/SingleForm';

export class Content extends React.Component {

  render() {
    return (
      <div className="user-signature-settings">
        <h1>Signature</h1>

        <SingleForm />
      </div>
    );
  }
}
